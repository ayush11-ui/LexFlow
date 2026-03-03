import { motion } from "framer-motion";
import { useState } from "react";
import { useNavigate } from "react-router-dom";
import { toast } from "sonner";
import { useAuth } from "../context/AuthContext";
import { Button } from "../components/ui/button";
import { Input } from "../components/ui/input";

export default function LoginPage() {
  const { login } = useAuth();
  const navigate = useNavigate();
  const [form, setForm] = useState({ email: "", password: "" });
  const [loading, setLoading] = useState(false);

  const onSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);
    try {
      await login(form.email, form.password);
      toast.success("Welcome to LexFlow");
      navigate("/dashboard");
    } catch {
      toast.error("Invalid credentials");
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="flex min-h-screen items-center justify-center p-6">
      <motion.form initial={{ opacity: 0, scale: 0.96 }} animate={{ opacity: 1, scale: 1 }} onSubmit={onSubmit} className="card-surface w-full max-w-md space-y-4 p-8">
        <h2 className="text-2xl font-bold">Sign in</h2>
        <Input placeholder="Email" type="email" value={form.email} onChange={(e) => setForm((v) => ({ ...v, email: e.target.value }))} required />
        <Input placeholder="Password" type="password" value={form.password} onChange={(e) => setForm((v) => ({ ...v, password: e.target.value }))} required />
        <Button type="submit" className="w-full" disabled={loading}>{loading ? "Signing in..." : "Login"}</Button>
      </motion.form>
    </div>
  );
}
