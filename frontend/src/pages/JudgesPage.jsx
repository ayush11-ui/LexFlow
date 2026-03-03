import { useEffect, useState } from "react";
import { api } from "../lib/api";
import { Card } from "../components/ui/card";
import { Button } from "../components/ui/button";

export default function JudgesPage() {
  const [judges, setJudges] = useState([]);
  useEffect(() => { api.get("/judges").then(({ data }) => setJudges(data.data || [])); }, []);

  return (
    <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
      {judges.map((j) => (
        <Card key={j.id}>
          <h3 className="text-lg font-bold">{j.name}</h3>
          <p className="text-sm text-slate-500">Specialization: {j.specialization || "General"}</p>
          <p className="mt-3 text-sm">Active cases: {j.assigned_cases_count}</p>
          <div className="mt-2 h-2 w-full rounded bg-slate-200"><div className="h-2 rounded bg-accent" style={{ width: `${j.utilization || 0}%` }} /></div>
          <Button className="mt-4 w-full" variant="outline">View Cases</Button>
        </Card>
      ))}
    </div>
  );
}
