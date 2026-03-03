import { useEffect, useMemo, useState } from "react";
import { BarChart, Bar, LineChart, Line, PieChart, Pie, Cell, ResponsiveContainer, XAxis, YAxis, Tooltip } from "recharts";
import { api } from "../lib/api";
import { Card } from "../components/ui/card";

export default function DashboardPage() {
  const [overview, setOverview] = useState(null);
  const [backlog, setBacklog] = useState([]);
  const [workload, setWorkload] = useState([]);

  useEffect(() => {
    Promise.all([api.get("/analytics/overview"), api.get("/analytics/backlog"), api.get("/analytics/workload")]).then(([o, b, w]) => {
      setOverview(o.data);
      setBacklog(b.data.trend || []);
      setWorkload(w.data.judges || []);
    });
  }, []);

  const cards = useMemo(() => [
    { label: "Total Active Cases", value: overview?.total_active_cases ?? 0 },
    { label: "Fast Track Cases", value: overview?.fast_track_cases ?? 0 },
    { label: "Standard Track", value: overview?.standard_track_cases ?? 0 },
    { label: "Complex Track", value: overview?.complex_track_cases ?? 0 },
  ], [overview]);

  const pieData = [
    { name: "Fast", value: overview?.fast_track_cases ?? 0, color: "#14B8A6" },
    { name: "Standard", value: overview?.standard_track_cases ?? 0, color: "#2563EB" },
    { name: "Complex", value: overview?.complex_track_cases ?? 0, color: "#F59E0B" },
  ];

  return (
    <div className="space-y-6">
      <div className="grid gap-4 md:grid-cols-4">
        {cards.map((card) => <Card key={card.label}><p className="text-sm text-slate-500">{card.label}</p><p className="mt-2 text-3xl font-bold">{card.value}</p></Card>)}
      </div>

      <div className="grid gap-4 md:grid-cols-2">
        <Card className="h-80"><h3 className="mb-3 font-semibold">Backlog Trend</h3><ResponsiveContainer width="100%" height="90%"><LineChart data={backlog}><XAxis dataKey="date" /><YAxis /><Tooltip /><Line dataKey="count" stroke="#2563EB" strokeWidth={2} /></LineChart></ResponsiveContainer></Card>
        <Card className="h-80"><h3 className="mb-3 font-semibold">Weekly Inflow</h3><ResponsiveContainer width="100%" height="90%"><BarChart data={backlog}><XAxis dataKey="date" /><YAxis /><Tooltip /><Bar dataKey="count" fill="#14B8A6" /></BarChart></ResponsiveContainer></Card>
      </div>

      <div className="grid gap-4 md:grid-cols-2">
        <Card className="h-80"><h3 className="mb-3 font-semibold">Track Distribution</h3><ResponsiveContainer width="100%" height="90%"><PieChart><Pie data={pieData} dataKey="value" nameKey="name" outerRadius={95}>{pieData.map((entry) => <Cell key={entry.name} fill={entry.color} />)}</Pie></PieChart></ResponsiveContainer></Card>
        <Card><h3 className="mb-3 font-semibold">Judge Workload</h3><div className="space-y-2">{workload.map((j) => <div key={j.id} className="rounded-xl border p-3"><p className="font-medium">{j.name}</p><p className="text-sm text-slate-500">Utilization: {j.utilization}%</p></div>)}</div></Card>
      </div>
    </div>
  );
}
