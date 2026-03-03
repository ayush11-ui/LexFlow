import { cn } from "../../lib/utils";

export function Button({ className, variant = "default", ...props }) {
  const variants = {
    default: "bg-secondary text-white hover:bg-blue-700",
    outline: "border border-slate-300 bg-white hover:bg-slate-50",
    danger: "bg-danger text-white hover:opacity-90",
    ghost: "hover:bg-slate-100",
  };

  return (
    <button
      className={cn(
        "inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition disabled:opacity-50",
        variants[variant],
        className
      )}
      {...props}
    />
  );
}
