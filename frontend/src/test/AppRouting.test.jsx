import { render, screen } from "@testing-library/react";
import { MemoryRouter } from "react-router-dom";
import { vi } from "vitest";
import App from "../App";

vi.mock("../context/AuthContext", () => ({
  useAuth: () => ({ user: null, loading: false }),
}));

describe("App routing", () => {
  it("redirects unauthenticated dashboard route to login", () => {
    render(
      <MemoryRouter initialEntries={["/dashboard"]}>
        <App />
      </MemoryRouter>
    );

    expect(screen.getByText(/sign in/i)).toBeInTheDocument();
  });
});
