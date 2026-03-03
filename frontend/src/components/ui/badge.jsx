import { cn } from "../../lib/utils";

export function Badge({ className, tone = "slate", ...props }) {
  const tones = {
    slate: "bg-slate-100 text-slate-700",
    fast: "bg-emerald-100 text-emerald-700",
    standard: "bg-blue-100 text-blue-700",
    complex: "bg-amber-100 text-amber-700",
    warning: "bg-warning/20 text-warning",
    danger: "bg-danger/20 text-danger",
  };
  return (
    <span className={cn("rounded-full px-2.5 py-1 text-xs font-medium", tones[tone] || tones.slate, className)} {...props} />
  );
}
