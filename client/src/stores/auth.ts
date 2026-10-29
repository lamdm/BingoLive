import { defineStore } from 'pinia';
import { computed, ref } from 'vue';
import { api, getToken, setToken } from '../lib/api';

export type User = {
  id: number;
  email: string;
  nickname: string;
  role: string;
  status: string;
  points: number;
};

export const useAuthStore = defineStore('auth', () => {
  const user = ref<User | null>(null);
  const loading = ref(false);
  const error = ref('');

  const isLoggedIn = computed(() => !!user.value);
  const isAdmin = computed(() => user.value?.role === 'ADMIN');
  const isHost = computed(() => user.value?.role === 'HOST' || user.value?.role === 'ADMIN');

  async function fetchMe() {
    if (!getToken()) {
      user.value = null;
      return;
    }
    try {
      const data = await api<{ user: User }>('/auth/me');
      user.value = data.user;
    } catch {
      setToken(null);
      user.value = null;
    }
  }

  async function login(email: string, password: string) {
    error.value = '';
    loading.value = true;
    try {
      const data = await api<{ token: string; user: User }>('/auth/login', {
        method: 'POST',
        body: JSON.stringify({ email, password }),
      });
      setToken(data.token);
      user.value = data.user;
    } catch (e: any) {
      error.value = e.message;
      throw e;
    } finally {
      loading.value = false;
    }
  }

  async function register(name: string, email: string, password: string, password_confirmation: string) {
    error.value = '';
    loading.value = true;
    try {
      const data = await api<{ token: string; user: User }>('/auth/register', {
        method: 'POST',
        body: JSON.stringify({ name, email, password, password_confirmation }),
      });
      setToken(data.token);
      user.value = data.user;
    } catch (e: any) {
      error.value = e.message;
      throw e;
    } finally {
      loading.value = false;
    }
  }

  async function logout() {
    try {
      await api('/auth/logout', { method: 'POST' });
    } catch {
      /* ignore */
    }
    setToken(null);
    user.value = null;
  }

  function setUser(u: User) {
    user.value = u;
  }

  return { user, loading, error, isLoggedIn, isAdmin, isHost, fetchMe, login, register, logout, setUser };
});
