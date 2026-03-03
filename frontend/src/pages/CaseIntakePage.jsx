import { useState } from "react";
import { useNavigate } from "react-router-dom";
import { toast } from "sonner";
import { api } from "../lib/api";
import { Card } from "../components/ui/card";
import { Input } from "../components/ui/input";
import { Select } from "../components/ui/select";
import { Button } from "../components/ui/button";

export default function CaseIntakePage() {
  const navigate = useNavigate();
  const [loading, setLoading] = useState(false);
  const [preview, setPreview] = useState({ predicted_track: "standard", priority_score: 0, estimated_duration: 1 });
  const [form, setForm] = useState({
    title: "",
    description: "",
    case_type: "civil",
    urgency_level: 3,
    complexity_level: "",
    estimated_duration: 1,
  });

  const previewClassification = async (next) => {
    try {
      const { data } = await api.post("/cases/classify/preview", next);
      setPreview(data);
    } catch {}
  };

  const onChange = (name, value) => {
    const next = { ...form, [name]: value };
    setForm(next);
    previewClassification(next);
  };

  const onSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);
    try {
      await api.post("/cases", form);
      toast.success("Case submitted");
      navigate("/queue");
    } catch {
      toast.error("Unable to submit case");
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="grid gap-6 md:grid-cols-2">
      <Card>
        <h2 className="mb-4 text-xl font-bold">Case Intake</h2>
        <form className="space-y-3" onSubmit={onSubmit}>
          <Input placeholder="Case title" value={form.title} onChange={(e) => onChange("title", e.target.value)} required />
          <Input placeholder="Description" value={form.description} onChange={(e) => onChange("description", e.target.value)} />
          <Select value={form.case_type} onChange={(e) => onChange("case_type", e.target.value)}>
            <option value="civil">Civil</option>
            <option value="criminal">Criminal</option>
            <option value="traffic">Traffic</option>
          </Select>
          <label className="block text-sm">Urgency: {form.urgency_level}</label>
          <input type="range" min="1" max="5" value={form.urgency_level} className="w-full" onChange={(e) => onChange("urgency_level", Number(e.target.value))} />
          <Input type="number" min="0.25" max="24" step="0.25" value={form.estimated_duration} onChange={(e) => onChange("estimated_duration", Number(e.target.value))} />
          <Input type="file" />
          <Button type="submit" disabled={loading}>{loading ? "Submitting..." : "Submit Case"}</Button>
        </form>
      </Card>
      <Card>
        <h3 className="text-lg font-semibold">Live Preview</h3>
        <div className="mt-4 space-y-2 text-sm">
          <p><span className="font-medium">Predicted Track:</span> {preview.predicted_track}</p>
          <p><span className="font-medium">Priority Score:</span> {preview.priority_score}</p>
          <p><span className="font-medium">Estimated Duration:</span> {preview.estimated_duration}h</p>
        </div>
      </Card>
    </div>
  );
}
