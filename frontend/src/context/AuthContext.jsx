import { createContext, useContext, useEffect, useMemo, useState } from "react";
import { api, authStorage } from "../lib/api";

const AuthContext = createContext(null);

export function AuthProvider({ children }) {
  const [user, setUser] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const token = authStorage.getToken();
    if (!token) {
      setLoading(false);
      return;
    }
    api.get("/me")
      .then(({ data }) => setUser(data?.data ?? data?.user ?? null))
      .catch(() => authStorage.clearToken())
      .finally(() => setLoading(false));
  }, []);

  const login = async (email, password) => {
    const { data } = await api.post("/login", { email, password });
    authStorage.setToken(data.token);
    setUser(data.user);
    return data.user;
  };

  const logout = async () => {
    try {
      await api.post("/logout");
    } finally {
      authStorage.clearToken();
      setUser(null);
    }
  };

  const value = useMemo(() => ({ user, setUser, loading, login, logout }), [user, loading]);
  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
}

export function useAuth() {
  return useContext(AuthContext);
}
