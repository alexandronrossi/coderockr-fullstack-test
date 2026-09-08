import { BrowserRouter, Navigate, Route, Routes } from 'react-router-dom';
import { RequireAuth } from '@/auth/RequireAuth';
import { AppShell } from '@/components/AppShell';
import { CreateInvestmentPage } from '@/pages/CreateInvestmentPage';
import { InvestmentDetailPage } from '@/pages/InvestmentDetailPage';
import { InvestmentsListPage } from '@/pages/InvestmentsListPage';
import { LoginPage } from '@/pages/LoginPage';
import { RegisterPage } from '@/pages/RegisterPage';

export default function App() {
  return (
    <BrowserRouter>
      <Routes>
        <Route path="/login" element={<LoginPage />} />
        <Route path="/register" element={<RegisterPage />} />
        <Route
          path="/investments"
          element={
            <RequireAuth>
              <AppShell>
                <InvestmentsListPage />
              </AppShell>
            </RequireAuth>
          }
        />
        <Route
          path="/investments/new"
          element={
            <RequireAuth>
              <AppShell>
                <CreateInvestmentPage />
              </AppShell>
            </RequireAuth>
          }
        />
        <Route
          path="/investments/:id"
          element={
            <RequireAuth>
              <AppShell>
                <InvestmentDetailPage />
              </AppShell>
            </RequireAuth>
          }
        />
        <Route path="/" element={<Navigate to="/investments" replace />} />
        <Route path="*" element={<Navigate to="/investments" replace />} />
      </Routes>
    </BrowserRouter>
  );
}
