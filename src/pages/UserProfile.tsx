import React, { useState, useEffect } from "react";
import { useNavigate } from "react-router-dom";
import { useAuth } from "@/context/AuthContext";
import Layout from "@/components/Layout";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs";
import { useToast } from "@/components/ui/use-toast";
import { User, Save, Key, RefreshCw } from "lucide-react";
import { Separator } from "@/components/ui/separator";
import { getApiUrl } from "@/config";

const UserProfile = () => {
  const { user, updateUser } = useAuth();
  const navigate = useNavigate();
  const { toast } = useToast();

  // Estados para os formulários
  const [profileData, setProfileData] = useState({
    name: "",
    email: "",
    document: "",
    phone: "",
    domain: ""
  });

  const [passwordData, setPasswordData] = useState({
    currentPassword: "",
    newPassword: "",
    confirmPassword: ""
  });

  const [isLoading, setIsLoading] = useState(false);
  const [isUpdatingPassword, setIsUpdatingPassword] = useState(false);
  const [documentType, setDocumentType] = useState("CPF/CNPJ");

  // Inicializar dados do perfil quando o usuário estiver disponível
  useEffect(() => {
    if (user) {
      console.log("Dados do usuário:", user);

      // Determinar o documento principal a mostrar (preferir CPF se ambos estiverem disponíveis)
      let documentToShow = "";
      let docType = "CPF/CNPJ";

      if (user.cpf && user.cpf.trim() !== "") {
        documentToShow = user.cpf;
        docType = "CPF";
      } else if (user.cnpj && user.cnpj.trim() !== "") {
        documentToShow = user.cnpj;
        docType = "CNPJ";
      } else if (user.document) {
        documentToShow = user.document;
        // Inferir o tipo do documento pelo tamanho
        const numericDoc = documentToShow.replace(/[^\d]/g, '');
        docType = numericDoc.length <= 11 ? "CPF" : "CNPJ";
      }

      setDocumentType(docType);
      setProfileData({
        name: user.name || "",
        email: user.email || "",
        document: documentToShow,
        phone: user.phone || "",
        domain: user.domain || user.dominio || ""
      });
    } else {
      navigate("/login");
    }
  }, [user, navigate]);

  // Detectar alterações no documento para atualizar o tipo
  useEffect(() => {
    if (profileData.document) {
      const numericDoc = profileData.document.replace(/[^\d]/g, '');
      if (numericDoc.length <= 11) {
        setDocumentType("CPF");
      } else {
        setDocumentType("CNPJ");
      }
    }
  }, [profileData.document]);

  // Atualizar dados do perfil
  const handleProfileUpdate = async (e: React.FormEvent) => {
    e.preventDefault();

    if (!user) return;

    setIsLoading(true);

    try {
      const token = localStorage.getItem("token");
      if (!token) {
        throw new Error("Token não encontrado");
      }

      console.log("Enviando dados para atualização:", {
        userId: user.id,
        name: profileData.name,
        email: profileData.email,
        document: profileData.document,
        phone: profileData.phone,
        domain: profileData.domain
      });

      // Limpar formatação do documento (remover caracteres não numéricos)
      const cleanDocument = profileData.document.replace(/[^\d]/g, '');

      const response = await fetch(getApiUrl('update_profile.php'), {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "Authorization": `Bearer ${token}`
        },
        body: JSON.stringify({
          userId: user.id,
          name: profileData.name,
          email: profileData.email,
          document: cleanDocument, // Enviar documento sem formatação
          phone: profileData.phone,
          domain: profileData.domain
        })
      });

      const responseText = await response.text();
      console.log("Resposta bruta da API:", responseText);

      let data;
      try {
        data = JSON.parse(responseText);
      } catch (parseError) {
        console.error("Erro ao processar resposta JSON:", parseError);
        throw new Error(`Erro ao processar resposta do servidor: ${responseText.substring(0, 100)}...`);
      }

      if (!response.ok) {
        throw new Error(data.error || "Erro ao atualizar perfil");
      }

      // Atualizar o contexto do usuário com os novos dados
      if (data.user) {
        updateUser(data.user);
      }

      toast({
        title: "Perfil atualizado",
        description: "Seus dados foram atualizados com sucesso",
      });
    } catch (error) {
      console.error("Erro ao atualizar perfil:", error);

      // Melhorar a mensagem de erro para o usuário
      let errorMessage = "Não foi possível atualizar o perfil";
      if (error instanceof Error) {
        console.error("Erro ao atualizar perfil:", error);

        // Extrair mensagem de erro para mostrar ao usuário
        errorMessage = error.message;

        // Tratar erros específicos
        if (errorMessage.includes("SQLSTATE[HY093]")) {
          errorMessage = "Erro de parâmetros no banco de dados. Por favor, verifique se todos os campos estão preenchidos corretamente.";
        } else if (errorMessage.includes("email já está em uso")) {
          errorMessage = "Este e-mail já está sendo usado por outro usuário. Por favor, use um e-mail diferente.";
        } else if (errorMessage.includes("CPF/CNPJ já está em uso")) {
          errorMessage = "Este CPF/CNPJ já está registrado por outro usuário.";
        } else if (errorMessage.includes("SQLSTATE")) {
          errorMessage = "Erro no banco de dados. Por favor, verifique seus dados e tente novamente mais tarde.";
        }
      }

      toast({
        title: "Erro ao atualizar perfil",
        description: errorMessage,
        variant: "destructive",
      });
    } finally {
      setIsLoading(false);
    }
  };

  // Atualizar senha
  const handlePasswordUpdate = async (e: React.FormEvent) => {
    e.preventDefault();

    if (!user) return;

    // Validar senhas
    if (passwordData.newPassword !== passwordData.confirmPassword) {
      toast({
        title: "Erro",
        description: "As senhas não coincidem",
        variant: "destructive",
      });
      return;
    }

    if (passwordData.newPassword.length < 6) {
      toast({
        title: "Erro",
        description: "A nova senha deve ter pelo menos 6 caracteres",
        variant: "destructive",
      });
      return;
    }

    setIsUpdatingPassword(true);

    try {
      const token = localStorage.getItem("token");
      if (!token) {
        throw new Error("Token não encontrado");
      }

      const response = await fetch(getApiUrl('update_password.php'), {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "Authorization": `Bearer ${token}`
        },
        body: JSON.stringify({
          userId: user.id,
          currentPassword: passwordData.currentPassword,
          newPassword: passwordData.newPassword
        })
      });

      const data = await response.json();

      if (!response.ok) {
        throw new Error(data.error || "Erro ao atualizar senha");
      }

      // Limpar formulário de senha
      setPasswordData({
        currentPassword: "",
        newPassword: "",
        confirmPassword: ""
      });

      toast({
        title: "Senha atualizada",
        description: "Sua senha foi atualizada com sucesso",
      });
    } catch (error) {
      console.error("Erro ao atualizar senha:", error);
      toast({
        title: "Erro",
        description: error instanceof Error ? error.message : "Não foi possível atualizar a senha",
        variant: "destructive",
      });
    } finally {
      setIsUpdatingPassword(false);
    }
  };

  if (!user) {
    return null;
  }

  return (
    <Layout>
      <div className="space-y-6">
        <div>
          <h1 className="text-3xl font-bold tracking-tight">Meu Perfil</h1>
          <p className="text-muted-foreground">
            Gerencie suas informações pessoais e senha
          </p>
        </div>

        <Tabs defaultValue="profile" className="space-y-4">
          <TabsList>
            <TabsTrigger value="profile" className="flex items-center gap-2">
              <User className="h-4 w-4" />
              Dados Pessoais
            </TabsTrigger>
            <TabsTrigger value="security" className="flex items-center gap-2">
              <Key className="h-4 w-4" />
              Segurança
            </TabsTrigger>
          </TabsList>

          <TabsContent value="profile" className="space-y-4">
            <Card>
              <CardHeader>
                <CardTitle>Dados Pessoais</CardTitle>
                <CardDescription>
                  Atualize suas informações pessoais
                </CardDescription>
              </CardHeader>
              <CardContent>
                <form onSubmit={handleProfileUpdate} className="space-y-4">
                  <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div className="space-y-2">
                      <Label htmlFor="name">Nome Completo</Label>
                      <Input
                        id="name"
                        value={profileData.name}
                        onChange={(e) => setProfileData({ ...profileData, name: e.target.value })}
                        required
                      />
                    </div>

                    <div className="space-y-2">
                      <Label htmlFor="email">E-mail</Label>
                      <Input
                        id="email"
                        type="email"
                        value={profileData.email}
                        onChange={(e) => setProfileData({ ...profileData, email: e.target.value })}
                        required
                      />
                    </div>

                    <div className="space-y-2">
                      <Label htmlFor="document">{documentType}</Label>
                      <Input
                        id="document"
                        value={profileData.document}
                        onChange={(e) => setProfileData({ ...profileData, document: e.target.value })}
                        required
                      />
                      <p className="text-xs text-muted-foreground">
                        {documentType === "CPF" ? "Formato: 000.000.000-00" : "Formato: 00.000.000/0000-00"}
                      </p>
                    </div>

                    <div className="space-y-2">
                      <Label htmlFor="phone">Telefone</Label>
                      <Input
                        id="phone"
                        value={profileData.phone}
                        onChange={(e) => setProfileData({ ...profileData, phone: e.target.value })}
                      />
                    </div>

                    <div className="space-y-2 md:col-span-2">
                      <Label htmlFor="domain">Domínio (opcional)</Label>
                      <Input
                        id="domain"
                        value={profileData.domain || ""}
                        onChange={(e) => setProfileData({ ...profileData, domain: e.target.value })}
                        placeholder="Exemplo: meusite.com.br"
                        disabled={user.isAdmin}
                      />
                      {user.isAdmin && (
                        <p className="text-xs text-muted-foreground mt-1">
                          Administradores não podem alterar o domínio
                        </p>
                      )}
                    </div>
                  </div>

                  <div className="flex justify-end">
                    <Button type="submit" disabled={isLoading}>
                      {isLoading ? (
                        <>
                          <RefreshCw className="mr-2 h-4 w-4 animate-spin" />
                          Salvando...
                        </>
                      ) : (
                        <>
                          <Save className="mr-2 h-4 w-4" />
                          Salvar Alterações
                        </>
                      )}
                    </Button>
                  </div>
                </form>
              </CardContent>
            </Card>
          </TabsContent>

          <TabsContent value="security" className="space-y-4">
            <Card>
              <CardHeader>
                <CardTitle>Alterar Senha</CardTitle>
                <CardDescription>
                  Atualize sua senha de acesso
                </CardDescription>
              </CardHeader>
              <CardContent>
                <form onSubmit={handlePasswordUpdate} className="space-y-4">
                  <div className="space-y-4">
                    <div className="space-y-2">
                      <Label htmlFor="currentPassword">Senha Atual</Label>
                      <Input
                        id="currentPassword"
                        type="password"
                        value={passwordData.currentPassword}
                        onChange={(e) => setPasswordData({ ...passwordData, currentPassword: e.target.value })}
                        required
                      />
                    </div>

                    <Separator />

                    <div className="space-y-2">
                      <Label htmlFor="newPassword">Nova Senha</Label>
                      <Input
                        id="newPassword"
                        type="password"
                        value={passwordData.newPassword}
                        onChange={(e) => setPasswordData({ ...passwordData, newPassword: e.target.value })}
                        required
                      />
                      <p className="text-xs text-muted-foreground">
                        A senha deve ter pelo menos 6 caracteres
                      </p>
                    </div>

                    <div className="space-y-2">
                      <Label htmlFor="confirmPassword">Confirmar Nova Senha</Label>
                      <Input
                        id="confirmPassword"
                        type="password"
                        value={passwordData.confirmPassword}
                        onChange={(e) => setPasswordData({ ...passwordData, confirmPassword: e.target.value })}
                        required
                      />
                    </div>
                  </div>

                  <div className="flex justify-end">
                    <Button type="submit" disabled={isUpdatingPassword}>
                      {isUpdatingPassword ? (
                        <>
                          <RefreshCw className="mr-2 h-4 w-4 animate-spin" />
                          Atualizando...
                        </>
                      ) : (
                        <>
                          <Key className="mr-2 h-4 w-4" />
                          Atualizar Senha
                        </>
                      )}
                    </Button>
                  </div>
                </form>
              </CardContent>
            </Card>
          </TabsContent>
        </Tabs>
      </div>
    </Layout>
  );
};

export default UserProfile;
