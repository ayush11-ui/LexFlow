import { render, screen } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import { MemoryRouter } from "react-router-dom";
import { vi } from "vitest";
import LoginPage from "../pages/LoginPage";
import { AuthProvider } from "../context/AuthContext";
const loginMock = vi.fn().mockResolvedValue({ id: 1, role: "admin" });

vi.mock("../context/AuthContext", async (importOriginal) => {
  const actual = await importOriginal();
  return {
    ...actual,
    useAuth: () => ({
      login: loginMock,
      user: null,
      loading: false,
    }),
  };
});

describe("LoginPage", () => {
  it("submits email and password", async () => {
    const user = userEvent.setup();
    render(
      <MemoryRouter>
        <AuthProvider>
          <LoginPage />
        </AuthProvider>
      </MemoryRouter>
    );

    await user.type(screen.getByPlaceholderText(/email/i), "admin@example.com");
    await user.type(screen.getByPlaceholderText(/password/i), "Password@123");
    await user.click(screen.getByRole("button", { name: /login/i }));
    expect(loginMock).toHaveBeenCalledWith("admin@example.com", "Password@123");
  });
});
