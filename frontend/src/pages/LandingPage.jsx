import { motion } from "framer-motion";
import { Link } from "react-router-dom";
import { Button } from "../components/ui/button";
import { Card } from "../components/ui/card";

const stats = [
  { label: "Cases Processed", value: "120K+" },
  { label: "Avg Scheduling Efficiency", value: "92%" },
  { label: "Courts Onboarded", value: "48" },
];

export default function LandingPage() {
  return (
    <div className="mx-auto max-w-7xl p-6 md:p-10">
      <motion.section initial={{ opacity: 0, y: 20 }} animate={{ opacity: 1, y: 0 }} className="card-surface p-10">
        <h1 className="text-4xl font-extrabold leading-tight md:text-5xl">Intelligent Case Flow Management for Modern Courts</h1>
        <p className="mt-4 max-w-2xl text-slate-600">LexFlow automates classification, priority scoring, and hearing scheduling for Differentiated Case Flow Management.</p>
        <div className="mt-8 flex gap-3">
          <Link to="/login"><Button>Login</Button></Link>
          <Button variant="outline">Request Demo</Button>
        </div>
      </motion.section>

      <section className="mt-8 grid gap-4 md:grid-cols-3">
        {stats.map((s) => <Card key={s.label}><p className="text-sm text-slate-500">{s.label}</p><p className="mt-2 text-2xl font-bold">{s.value}</p></Card>)}
      </section>
    </div>
  );
}
