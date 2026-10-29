const API_URL = import.meta.env.VITE_API_URL || 'http://127.0.0.1:8000';

export function getToken() {
  return localStorage.getItem('bl_token');
}

export function setToken(token: string | null) {
  if (token) localStorage.setItem('bl_token', token);
  else localStorage.removeItem('bl_token');
}

export async function api<T = any>(path: string, options: RequestInit = {}): Promise<T> {
  const headers: Record<string, string> = {
    Accept: 'application/json',
    'Content-Type': 'application/json',
    ...(options.headers as Record<string, string>),
  };
  const token = getToken();
  if (token) headers.Authorization = `Bearer ${token}`;

  const res = await fetch(`${API_URL}/api${path}`, { ...options, headers });
  const data = await res.json().catch(() => ({} as Record<string, any>));
  if (!res.ok) {
    const firstError = data.errors ? (Object.values(data.errors)[0] as string[] | undefined)?.[0] : undefined;
    throw new Error(data.message || firstError || `Lỗi ${res.status}`);
  }
  return data as T;
}

export { API_URL };
