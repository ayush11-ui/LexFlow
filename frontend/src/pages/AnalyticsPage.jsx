import { useEffect, useState } from "react";
import { PieChart, Pie, Cell, ResponsiveContainer, Tooltip } from "recharts";
import { api } from "../lib/api";
import { Card } from "../components/ui/card";
import { Input } from "../components/ui/input";

export default function AnalyticsPage() {
  const [range, setRange] = useState({ from: "", to: "" });
  const [overview, setOverview] = useState(null);
  const [workload, setWorkload] = useState(null);

  useEffect(() => {
    Promise.all([api.get("/analytics/overview"), api.get("/analytics/workload")]).then(([o, w]) => {
      setOverview(o.data);
      setWorkload(w.data);
    });
  }, []);

  const distribution = Object.entries(workload?.case_distribution || {}).map(([name, value], i) => ({
    name,
    value,
    color: ["#14B8A6", "#2563EB", "#F59E0B"][i % 3],
  }));

  return (
    <div className="space-y-4">
      <Card className="flex flex-wrap gap-3">
        <Input type="date" value={range.from} onChange={(e) => setRange((v) => ({ ...v, from: e.target.value }))} />
        <Input type="date" value={range.to} onChange={(e) => setRange((v) => ({ ...v, to: e.target.value }))} />
      </Card>
      <div className="grid gap-4 md:grid-cols-3">
        <Card><p className="text-sm text-slate-500">Average disposal time</p><p className="mt-2 text-2xl font-bold">{overview?.average_disposal_time_days ?? 0} days</p></Card>
        <Card><p className="text-sm text-slate-500">Scheduling efficiency</p><p className="mt-2 text-2xl font-bold">{overview?.scheduling_efficiency ?? 0}%</p></Card>
        <Card><p className="text-sm text-slate-500">Active cases</p><p className="mt-2 text-2xl font-bold">{overview?.total_active_cases ?? 0}</p></Card>
      </div>
      <Card className="h-96">
        <h3 className="mb-2 font-semibold">Case Distribution</h3>
        <ResponsiveContainer width="100%" height="90%">
          <PieChart>
            <Pie data={distribution} dataKey="value" nameKey="name" outerRadius={120}>
              {distribution.map((d) => <Cell key={d.name} fill={d.color} />)}
            </Pie>
            <Tooltip />
          </PieChart>
        </ResponsiveContainer>
      </Card>
    </div>
  );
}
