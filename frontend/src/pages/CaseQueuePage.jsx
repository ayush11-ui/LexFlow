import { useEffect, useState } from "react";
import { toast } from "sonner";
import { api } from "../lib/api";
import { Badge } from "../components/ui/badge";
import { Button } from "../components/ui/button";
import { Card } from "../components/ui/card";
import { Select } from "../components/ui/select";

export default function CaseQueuePage() {
  const [cases, setCases] = useState([]);
  const [filters, setFilters] = useState({ track: "", urgency_level: "", status: "" });

  const loadCases = async () => {
    const { data } = await api.get("/cases", { params: filters });
    setCases(data.data || []);
  };

  useEffect(() => { loadCases(); }, [filters.track, filters.urgency_level, filters.status]);

  const autoSchedule = async (caseId) => {
    try {
      await api.post("/schedule/auto", { case_id: caseId });
      toast.success("Auto-scheduling attempted");
      loadCases();
    } catch {
      toast.error("Scheduling failed");
    }
  };

  return (
    <Card>
      <div className="mb-4 grid gap-3 md:grid-cols-3">
        <Select value={filters.track} onChange={(e) => setFilters((v) => ({ ...v, track: e.target.value }))}><option value="">All Tracks</option><option value="fast">Fast</option><option value="standard">Standard</option><option value="complex">Complex</option></Select>
        <Select value={filters.urgency_level} onChange={(e) => setFilters((v) => ({ ...v, urgency_level: e.target.value }))}><option value="">All Urgency</option><option value="5">5</option><option value="4">4</option><option value="3">3</option><option value="2">2</option><option value="1">1</option></Select>
        <Select value={filters.status} onChange={(e) => setFilters((v) => ({ ...v, status: e.target.value }))}><option value="">All Status</option><option value="pending">Pending</option><option value="scheduled">Scheduled</option><option value="completed">Completed</option></Select>
      </div>
      <div className="overflow-auto">
        <table className="min-w-full text-left text-sm">
          <thead><tr className="border-b"><th className="p-2">Case</th><th className="p-2">Track</th><th className="p-2">Priority</th><th className="p-2">Status</th><th className="p-2">Actions</th></tr></thead>
          <tbody>
            {cases.map((c) => (
              <tr key={c.id} className="border-b">
                <td className="p-2">{c.case_number}</td>
                <td className="p-2"><Badge tone={c.track}>{c.track}</Badge></td>
                <td className="p-2"><Badge tone="warning">{c.priority_score}</Badge></td>
                <td className="p-2">{c.status}</td>
                <td className="p-2 space-x-2">
                  <Button variant="outline">Assign Judge</Button>
                  <Button onClick={() => autoSchedule(c.id)}>Schedule</Button>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </Card>
  );
}
