import { Link, useLocation, useNavigate } from "react-router-dom";
import { useAuth } from "../../context/AuthContext";
import { Button } from "../ui/button";

const navItems = [
  { to: "/dashboard", label: "Dashboard" },
  { to: "/intake", label: "Case Intake" },
  { to: "/queue", label: "Case Queue" },
  { to: "/scheduling", label: "Scheduling" },
  { to: "/judges", label: "Judges" },
  { to: "/analytics", label: "Analytics" },
];

export default function AppLayout({ children }) {
  const { pathname } = useLocation();
  const navigate = useNavigate();
  const { logout, user } = useAuth();

  return (
    <div className="min-h-screen">
      <header className="sticky top-0 z-10 border-b bg-white/90 backdrop-blur">
        <div className="mx-auto flex max-w-7xl items-center justify-between px-6 py-4">
          <Link to="/dashboard" className="text-xl font-extrabold">LexFlow</Link>
          <nav className="hidden gap-2 md:flex">
            {navItems.map((item) => (
              <Link
                key={item.to}
                to={item.to}
                className={`rounded-xl px-3 py-2 text-sm ${pathname === item.to ? "bg-primary text-white" : "hover:bg-slate-100"}`}
              >
                {item.label}
              </Link>
            ))}
          </nav>
          <div className="flex items-center gap-3">
            <span className="text-sm text-slate-600">{user?.name} ({user?.role})</span>
            <Button
              variant="outline"
              onClick={async () => {
                await logout();
                navigate("/login");
              }}
            >
              Logout
            </Button>
          </div>
        </div>
      </header>
      <main className="mx-auto max-w-7xl p-6">{children}</main>
    </div>
  );
}
