import React from "react";
import { useNavigate, useLocation } from "react-router-dom";
import { useAuth } from "@/context/AuthContext";
import {
  LayoutDashboard,
  CreditCard,
  Building2,
  Users,
  LogOut,
  Menu,
  X,
  LogIn,
  History,
  FileSearch,
  Shield,
  User,
  Key,
  Settings,
  DollarSign,
  Globe,
  Receipt,
  ActivitySquare
} from "lucide-react";
import { Button } from "@/components/ui/button";
import { Separator } from "@/components/ui/separator";
import { Sheet, SheetContent, SheetTrigger } from "@/components/ui/sheet";
import { useIsMobile } from "@/hooks/use-mobile";

interface LayoutProps {
  children: React.ReactNode;
}

const Layout: React.FC<LayoutProps> = ({ children }) => {
  const { user, logout } = useAuth();
  const navigate = useNavigate();
  const location = useLocation();
  const isMobile = useIsMobile();
  const [open, setOpen] = React.useState(false);

  const navigation = [
    { name: "Painel Admin", href: "/admin", icon: Shield, requiresAdmin: true },
    { name: "Dashboard", href: "/dashboard", icon: LayoutDashboard, requiresAuth: true, hideForAdmin: true },
    { name: "Recarga", href: "/recarga", icon: CreditCard, requiresAuth: true, hideForAdmin: true },
    { name: "Registro de Consultas", href: "/registro-consultas", icon: History, requiresAuth: true, hideForAdmin: true },
    { name: "Histórico de Transações", href: "/transacoes", icon: Receipt, requiresAuth: true },
    { name: "Meu Perfil", href: "/perfil", icon: User, requiresAuth: true },
    { name: "Gerenciar Usuários", href: "/gerenciar-usuarios", icon: Users, requiresAdmin: true },
    { name: "Gerenciar Tokens", href: "/gerenciar-tokens", icon: Key, requiresAdmin: true },
    { name: "Teste de API", href: "/api-test", icon: ActivitySquare, requiresAdmin: true },
  ];

  const handleNavigation = (href: string) => {
    navigate(href);
    setOpen(false);
  };

  const isActive = (path: string) => location.pathname === path;

  const renderNavLinks = () => (
    <div className="space-y-1">
      {navigation.map((item) => {
        // Skip if item requires authentication and user is not logged in
        if (item.requiresAuth && !user) return null;

        // Skip if item requires admin and user is not an admin
        if (item.requiresAdmin && (!user || !user.isAdmin)) return null;

        // Skip if item should be hidden for admin and user is an admin
        if (item.hideForAdmin && user?.isAdmin) return null;

        return (
          <Button
            key={item.name}
            variant={isActive(item.href) ? "secondary" : "ghost"}
            className={`w-full justify-start ${isActive(item.href) ? "bg-primary/10" : ""}`}
            onClick={() => handleNavigation(item.href)}
          >
            <item.icon className="mr-2 h-4 w-4" />
            {item.name}
          </Button>
        );
      })}
    </div>
  );

  const renderDesktopSidebar = () => (
    <div className="hidden md:flex md:flex-col md:w-64 md:fixed md:inset-y-0 border-r bg-card">
      <div className="flex flex-col flex-grow pt-5 overflow-y-auto">
        <div className="flex items-center flex-shrink-0 px-4">
          <h1 className="text-xl font-bold tracking-tight">
            <span className="text-blue-600">Gov</span>Nex
          </h1>
        </div>
        <div className="mt-5 flex-grow flex flex-col px-3">
          {renderNavLinks()}
          <div className="mt-auto pb-4">
            <Separator className="my-4" />
            {user ? (
              <>
                <div className="px-3 py-2 flex items-center space-x-3">
                  <div className="h-8 w-8 rounded-full bg-primary/10 flex items-center justify-center">
                    <User className="h-4 w-4 text-primary" />
                  </div>
                  <div>
                    <p className="text-sm font-medium truncate max-w-[140px]">{user.name}</p>
                    <p className="text-xs text-muted-foreground truncate max-w-[140px]">{user.email}</p>
                  </div>
                </div>
                {user.domain && (
                  <div className="px-3 py-1 mb-2">
                    <div className="flex items-center text-xs text-muted-foreground">
                      <Globe className="h-3 w-3 mr-1" />
                      <span className="truncate">{user.domain}</span>
                    </div>
                  </div>
                )}
                {!user.isAdmin && (
                  <div className="px-3 py-1 mb-2">
                    <div className="flex items-center text-xs font-medium text-green-600">
                      <DollarSign className="h-3 w-3 mr-1" />
                      <span>Saldo: R$ {typeof user.balance === 'number' ? user.balance.toFixed(2) : '0.00'}</span>
                    </div>
                  </div>
                )}
                <Button
                  variant="ghost"
                  className="w-full justify-start text-red-500 hover:text-red-600 hover:bg-red-50"
                  onClick={() => {
                    logout();
                    navigate("/");
                  }}
                >
                  <LogOut className="mr-2 h-4 w-4" />
                  Sair
                </Button>
              </>
            ) : (
              <Button
                variant="ghost"
                className="w-full justify-start"
                onClick={() => navigate("/login")}
              >
                <LogIn className="mr-2 h-4 w-4" />
                Entrar
              </Button>
            )}
          </div>
        </div>
      </div>
    </div>
  );

  const renderMobileHeader = () => (
    <div className="md:hidden flex items-center justify-between p-4 border-b">
      <h1 className="text-xl font-bold tracking-tight">
        <span className="text-blue-600">Gov</span>Nex
      </h1>
      <Sheet open={open} onOpenChange={setOpen}>
        <SheetTrigger asChild>
          <Button variant="ghost" size="icon">
            <Menu className="h-6 w-6" />
          </Button>
        </SheetTrigger>
        <SheetContent side="left" className="w-64 sm:max-w-sm">
          <div className="flex flex-col h-full py-6">
            <div className="flex items-center justify-between mb-6">
              <h2 className="text-lg font-semibold">Menu</h2>
              <Button variant="ghost" size="icon" onClick={() => setOpen(false)}>
                <X className="h-4 w-4" />
              </Button>
            </div>
            <div className="flex-1">{renderNavLinks()}</div>
            <div className="mt-auto pt-4">
              <Separator className="mb-4" />
              {user ? (
                <>
                  <div className="px-1 py-2 flex items-center space-x-3">
                    <div className="h-8 w-8 rounded-full bg-primary/10 flex items-center justify-center">
                      <User className="h-4 w-4 text-primary" />
                    </div>
                    <div>
                      <p className="text-sm font-medium">{user.name}</p>
                      <p className="text-xs text-muted-foreground">{user.email}</p>
                    </div>
                  </div>
                  {user.domain && (
                    <div className="px-1 py-1 mb-2">
                      <div className="flex items-center text-xs text-muted-foreground">
                        <Globe className="h-3 w-3 mr-1" />
                        <span>{user.domain}</span>
                      </div>
                    </div>
                  )}
                  {!user.isAdmin && (
                    <div className="px-1 py-1 mb-2">
                      <div className="flex items-center text-xs font-medium text-green-600">
                        <DollarSign className="h-3 w-3 mr-1" />
                        <span>Saldo: R$ {typeof user.balance === 'number' ? user.balance.toFixed(2) : '0.00'}</span>
                      </div>
                    </div>
                  )}
                  <Button
                    variant="ghost"
                    className="w-full justify-start text-red-500 hover:text-red-600 hover:bg-red-50"
                    onClick={() => {
                      logout();
                      navigate("/");
                      setOpen(false);
                    }}
                  >
                    <LogOut className="mr-2 h-4 w-4" />
                    Sair
                  </Button>
                </>
              ) : (
                <Button
                  variant="ghost"
                  className="w-full justify-start"
                  onClick={() => {
                    navigate("/login");
                    setOpen(false);
                  }}
                >
                  <LogIn className="mr-2 h-4 w-4" />
                  Entrar
                </Button>
              )}
            </div>
          </div>
        </SheetContent>
      </Sheet>
    </div>
  );

  return (
    <div className="flex h-screen bg-background">
      {renderDesktopSidebar()}
      <div className="flex flex-col flex-1 md:pl-64">
        {renderMobileHeader()}
        <main className="flex-1 p-4 md:p-8 overflow-y-auto">{children}</main>
      </div>
    </div>
  );
};

export default Layout;
