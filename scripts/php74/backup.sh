#!/usr/bin/env bash
# Backup PHP 7.4 trên server trước khi vá.
# Điền thông tin SSH bên dưới, rồi chạy: bash backup.sh
# Chạy file này trước patch.sh.

SSH_USER="bc-dev"
SSH_HOST=""
SSH_PORT="63439"
SSH_IDENTITY=""

set -euo pipefail

if [[ -z "$SSH_HOST" ]]; then
  echo "Điền SSH_HOST ở đầu file backup.sh" >&2
  exit 1
fi

SSH_OPTS=(
  -p "$SSH_PORT"
  -o ServerAliveInterval=60
  -o ServerAliveCountMax=30
  -o ConnectTimeout=20
  -o StrictHostKeyChecking=accept-new
)
SCP_OPTS=(
  -P "$SSH_PORT"
  -o ServerAliveInterval=60
  -o ServerAliveCountMax=30
  -o ConnectTimeout=20
  -o StrictHostKeyChecking=accept-new
)
if [[ -n "$SSH_IDENTITY" ]]; then
  SSH_OPTS+=(-i "$SSH_IDENTITY" -o IdentitiesOnly=yes)
  SCP_OPTS+=(-i "$SSH_IDENTITY" -o IdentitiesOnly=yes)
fi

REMOTE="$(mktemp)"
trap 'rm -f "$REMOTE"' EXIT

cat > "$REMOTE" << 'REMOTE_SCRIPT'
#!/bin/bash
set -euo pipefail

if [[ "$(id -u)" -ne 0 ]]; then
  echo "Script remote phải chạy bằng sudo." >&2
  exit 1
fi

if [[ ! -x /usr/sbin/php-fpm || ! -x /usr/bin/php ]]; then
  echo "Không thấy /usr/sbin/php-fpm hoặc /usr/bin/php" >&2
  exit 1
fi

if ! php -v | head -n 1 | grep -q 'PHP 7\.4'; then
  echo "PHP đang chạy không phải 7.4:" >&2
  php -v | head -n 1 >&2
  exit 1
fi

install -d -m 700 /root/php-backup
exec > >(tee -a /root/php-backup/backup.log) 2>&1
echo "===== backup $(date -Is) ====="

cd /root/php-backup
rpm -qa 'php*' | sort > installed-php-packages.txt
if [[ ! -s installed-php-packages.txt ]]; then
  echo "Không có gói php nào được cài." >&2
  exit 1
fi

: > files.list
while IFS= read -r owner; do
  while IFS= read -r path; do
    if [[ -f "$path" ]]; then
      install -d "files$(dirname "$path")"
      cp -a "$path" "files$path"
      printf '%s\n' "$path" >> files.list
    fi
  done < <(rpm -ql "$owner")
done < installed-php-packages.txt

config_paths=()
for relative in etc/php.ini etc/php.d etc/php-fpm.conf etc/php-fpm.d; do
  if [[ -e "/$relative" ]]; then
    config_paths+=("$relative")
  fi
done
if [[ ${#config_paths[@]} -gt 0 ]]; then
  tar -C / -czf /root/php-backup/etc-php.tar.gz "${config_paths[@]}"
fi

cp -a /usr/sbin/php-fpm /root/php-backup/php-fpm
cp -a /usr/bin/php /root/php-backup/php
php-config --configure-options > /root/php-backup/configure-options.txt

test -s /root/php-backup/php-fpm
test -s /root/php-backup/php
test -s /root/php-backup/configure-options.txt
test -s /root/php-backup/files.list

echo "BACKUP_OK"
ls -lh /root/php-backup/php-fpm /root/php-backup/php /root/php-backup/etc-php.tar.gz
echo "Số gói: $(wc -l < /root/php-backup/installed-php-packages.txt)"
echo "Số file: $(wc -l < /root/php-backup/files.list)"
REMOTE_SCRIPT

echo "Đang copy script backup lên ${SSH_USER}@${SSH_HOST}"
scp "${SCP_OPTS[@]}" "$REMOTE" "${SSH_USER}@${SSH_HOST}:/tmp/php74-backup-remote.sh"
echo "Nhập mật khẩu sudo trên server nếu được hỏi."
ssh -tt "${SSH_OPTS[@]}" "${SSH_USER}@${SSH_HOST}" "sudo bash /tmp/php74-backup-remote.sh; status=\$?; rm -f /tmp/php74-backup-remote.sh; exit \$status"
