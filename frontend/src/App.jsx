import { Navigate, Route, Routes } from "react-router-dom";
import { useAuth } from "./context/AuthContext";
import AppLayout from "./components/layouts/AppLayout";
import LandingPage from "./pages/LandingPage";
import LoginPage from "./pages/LoginPage";
import DashboardPage from "./pages/DashboardPage";
import CaseIntakePage from "./pages/CaseIntakePage";
import CaseQueuePage from "./pages/CaseQueuePage";
import SchedulingPage from "./pages/SchedulingPage";
import JudgesPage from "./pages/JudgesPage";
import AnalyticsPage from "./pages/AnalyticsPage";

function Protected({ children }) {
  const { user, loading } = useAuth();
  if (loading) return <div className="p-8">Loading...</div>;
  if (!user) return <Navigate to="/login" replace />;
  return <AppLayout>{children}</AppLayout>;
}

export default function App() {
  return (
    <Routes>
      <Route path="/" element={<LandingPage />} />
      <Route path="/login" element={<LoginPage />} />
      <Route path="/dashboard" element={<Protected><DashboardPage /></Protected>} />
      <Route path="/intake" element={<Protected><CaseIntakePage /></Protected>} />
      <Route path="/queue" element={<Protected><CaseQueuePage /></Protected>} />
      <Route path="/scheduling" element={<Protected><SchedulingPage /></Protected>} />
      <Route path="/judges" element={<Protected><JudgesPage /></Protected>} />
      <Route path="/analytics" element={<Protected><AnalyticsPage /></Protected>} />
      <Route path="*" element={<Navigate to="/" replace />} />
    </Routes>
  );
}
