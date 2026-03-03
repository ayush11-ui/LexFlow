import { useEffect, useState } from "react";
import FullCalendar from "@fullcalendar/react";
import dayGridPlugin from "@fullcalendar/daygrid";
import timeGridPlugin from "@fullcalendar/timegrid";
import interactionPlugin from "@fullcalendar/interaction";
import { toast } from "sonner";
import { api } from "../lib/api";
import { Card } from "../components/ui/card";
import { Button } from "../components/ui/button";
import { Select } from "../components/ui/select";

export default function SchedulingPage() {
  const [events, setEvents] = useState([]);
  const [judges, setJudges] = useState([]);
  const [judgeId, setJudgeId] = useState("");

  const load = async () => {
    const [eRes, jRes] = await Promise.all([
      api.get("/schedule/events", { params: judgeId ? { judge_id: judgeId } : {} }),
      api.get("/judges"),
    ]);
    setEvents((eRes.data.data || []).map((e) => ({
      id: e.id,
      title: `${e.court_case?.case_number || "Case"} (${e.courtroom})`,
      start: `${e.hearing_date}T${e.start_time}`,
      end: `${e.hearing_date}T${e.end_time}`,
    })));
    setJudges(jRes.data.data || []);
  };

  useEffect(() => { load(); }, [judgeId]);

  return (
    <Card>
      <div className="mb-4 flex flex-wrap items-center gap-3">
        <Select className="max-w-xs" value={judgeId} onChange={(e) => setJudgeId(e.target.value)}>
          <option value="">All Judges</option>
          {judges.map((j) => <option key={j.id} value={j.id}>{j.name}</option>)}
        </Select>
        <Button onClick={async () => { await api.post("/schedule/auto"); toast.success("Auto-schedule completed"); load(); }}>Auto-schedule</Button>
      </div>
      <FullCalendar
        plugins={[dayGridPlugin, timeGridPlugin, interactionPlugin]}
        initialView="timeGridWeek"
        editable
        events={events}
        eventDrop={() => toast.info("Drag-drop captured. Persist manual updates via /schedule/manual.")}
      />
    </Card>
  );
}
