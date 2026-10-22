#!/usr/bin/env bash
# Vá PHP 7.4 bằng nhánh Git PHP-7.4-CVE-2026-109618-109619, rồi kiểm tra.
# Điền thông tin SSH bên dưới, rồi chạy: bash patch.sh
# Chạy backup.sh trước. Script này dừng nếu chưa có backup.

SSH_USER="bc-dev"
SSH_HOST=""
SSH_PORT="63439"
SSH_IDENTITY=""

set -euo pipefail

if [[ -z "$SSH_HOST" ]]; then
  echo "Điền SSH_HOST ở đầu file patch.sh" >&2
  exit 1
fi

SSH_OPTS=(
  -p "$SSH_PORT"
  -o ServerAliveInterval=60
  -o ServerAliveCountMax=120
  -o ConnectTimeout=20
  -o StrictHostKeyChecking=accept-new
)
SCP_OPTS=(
  -P "$SSH_PORT"
  -o ServerAliveInterval=60
  -o ServerAliveCountMax=120
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

EXPECTED_COMMIT="712e60f9edfd03f0960fc4fc3c2c375f42b90aa2"
BRANCH="PHP-7.4-CVE-2026-109618-109619"
SRC="/root/php-build/php-src"

if [[ "$(id -u)" -ne 0 ]]; then
  echo "Script remote phải chạy bằng sudo." >&2
  exit 1
fi

restore_backup() {
  echo "Đang khôi phục bản PHP đã backup..."
  systemctl stop php-fpm || true
  if [[ -d /root/php-backup/files ]]; then
    cp -a /root/php-backup/files/. /
  fi
  if [[ -f /root/php-backup/etc-php.tar.gz ]]; then
    tar -C / -xzf /root/php-backup/etc-php.tar.gz
  fi
  restorecon -Rv /usr/sbin/php-fpm /usr/bin/php /usr/lib64/php || true
  systemctl start php-fpm || true
}

if [[ ! -s /root/php-backup/php-fpm || ! -s /root/php-backup/configure-options.txt || ! -d /root/php-backup/files ]]; then
  echo "Chưa có backup. Chạy backup.sh trước." >&2
  exit 1
fi

if ! php -v | head -n 1 | grep -q 'PHP 7\.4'; then
  echo "PHP đang chạy không phải 7.4:" >&2
  php -v | head -n 1 >&2
  exit 1
fi

install -d -m 700 /root/php-backup
exec > >(tee -a /root/php-backup/patch.log) 2>&1
echo "===== patch $(date -Is) ====="

dnf install -y git gcc make autoconf bison re2c pkgconf-pkg-config \
  libxml2-devel sqlite-devel openssl-devel libcurl-devel \
  oniguruma-devel libpng-devel libjpeg-turbo-devel freetype-devel \
  systemd-devel bzip2-devel libxslt-devel gmp-devel libicu-devel \
  openldap-devel readline-devel libsodium-devel krb5-devel libffi-devel

install -d /root/php-build
if [[ ! -d "$SRC/.git" ]]; then
  git clone --depth 1 --branch "$BRANCH" https://github.com/php/php-src.git "$SRC"
fi
cd "$SRC"
git fetch --depth 1 origin "$BRANCH"
git checkout --force FETCH_HEAD

head_commit="$(git rev-parse HEAD)"
if [[ "$head_commit" != "$EXPECTED_COMMIT" ]]; then
  echo "Sai commit: $head_commit" >&2
  exit 1
fi
echo "Commit đúng: $head_commit"

./buildconf
# shellcheck disable=SC2046
eval "./configure $(cat /root/php-backup/configure-options.txt)" | tee /root/php-backup/configure.out
if ! grep -q 'Thank you for using PHP' /root/php-backup/configure.out; then
  echo "configure không thành công. Xem /root/php-backup/configure.out" >&2
  exit 1
fi

make -j"$(nproc)"
make install
restorecon -v /usr/sbin/php-fpm /usr/bin/php || true
systemctl restart php-fpm

fail=0

marker="Possible integer overflow in memory allocation"
if strings /usr/sbin/php-fpm | grep -F -q "$marker"; then
  echo "OK: /usr/sbin/php-fpm đã có bản vá 2026"
else
  echo "FAIL: /usr/sbin/php-fpm chưa có bản vá 2026"
  fail=1
fi

if strings /root/php-backup/php-fpm | grep -F -q "$marker"; then
  echo "FAIL: file backup cũng chứa bản vá 2026"
  fail=1
else
  echo "OK: file backup là bản cũ"
fi

php_state="$(systemctl is-active php-fpm || true)"
nginx_state="$(systemctl is-active nginx || true)"
echo "php-fpm: $php_state"
echo "nginx: $nginx_state"
if [[ "$php_state" != "active" ]]; then
  fail=1
fi
if [[ "$nginx_state" != "active" ]]; then
  echo "FAIL: nginx không active"
  fail=1
fi

if [[ "$fail" -ne 0 ]]; then
  restore_backup
  echo "PATCH_FAILED"
  exit 1
fi

echo "PATCH_OK"
php -v | head -n 1
REMOTE_SCRIPT

echo "Đang copy script vá lên ${SSH_USER}@${SSH_HOST}"
scp "${SCP_OPTS[@]}" "$REMOTE" "${SSH_USER}@${SSH_HOST}:/tmp/php74-patch-remote.sh"
echo "Nhập mật khẩu sudo trên server nếu được hỏi. Bước make có thể chạy nhiều phút."
ssh -tt "${SSH_OPTS[@]}" "${SSH_USER}@${SSH_HOST}" "sudo bash /tmp/php74-patch-remote.sh; status=\$?; rm -f /tmp/php74-patch-remote.sh; exit \$status"
