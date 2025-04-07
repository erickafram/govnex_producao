import { Toaster } from "@/components/ui/toaster";
import { Toaster as Sonner } from "@/components/ui/sonner";
import { TooltipProvider } from "@/components/ui/tooltip";
import { QueryClient, QueryClientProvider } from "@tanstack/react-query";
import { BrowserRouter, Routes, Route } from "react-router-dom";
import { AuthProvider } from "@/context/AuthContext";
import Index from "./pages/Index";
import Login from "./pages/Login";
import Register from "./pages/Register";
import Dashboard from "./pages/Dashboard";
import Recarga from "./pages/Recarga";
import CNPJConsult from "./pages/CNPJConsult";
import Admin from "./pages/Admin";
import AdminDashboard from "./pages/AdminDashboard";
import RegistroConsultas from "./pages/RegistroConsultas";
import TokenManagement from "./pages/TokenManagement";
import UserManagement from "./pages/UserManagement";
import TransactionHistory from "./pages/TransactionHistory";
import UserProfile from "./pages/UserProfile";
import NotFound from "./pages/NotFound";
import ApiTest from "./pages/ApiTest";

const queryClient = new QueryClient();

const App = () => (
  <QueryClientProvider client={queryClient}>
    <AuthProvider>
      <TooltipProvider>
        <Toaster />
        <Sonner />
        <BrowserRouter>
          <Routes>
            <Route path="/" element={<Index />} />
            <Route path="/login" element={<Login />} />
            <Route path="/registro" element={<Register />} />
            <Route path="/dashboard" element={<Dashboard />} />
            <Route path="/recarga" element={<Recarga />} />
            <Route path="/consulta-cnpj" element={<CNPJConsult />} />
            <Route path="/registro-consultas" element={<RegistroConsultas />} />
            <Route path="/transacoes" element={<TransactionHistory />} />
            <Route path="/admin" element={<AdminDashboard />} />
            <Route path="/gerenciar-tokens" element={<TokenManagement />} />
            <Route path="/gerenciar-usuarios" element={<UserManagement />} />
            <Route path="/perfil" element={<UserProfile />} />
            <Route path="/api-test" element={<ApiTest />} />
            <Route path="*" element={<NotFound />} />
          </Routes>
        </BrowserRouter>
      </TooltipProvider>
    </AuthProvider>
  </QueryClientProvider>
);

export default App;
