
import React, { useState } from "react";
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Separator } from "@/components/ui/separator";
import { Copy, Check, RefreshCw } from "lucide-react";
import { useToast } from "@/components/ui/use-toast";

interface PixQRCodeProps {
  amount: number;
  pixCode: string;
  qrCodeUrl: string;
  onRefresh?: () => void;
  isLoading?: boolean;
}

const PixQRCode: React.FC<PixQRCodeProps> = ({ 
  amount, 
  pixCode, 
  qrCodeUrl, 
  onRefresh, 
  isLoading = false 
}) => {
  const { toast } = useToast();
  const [copied, setCopied] = useState(false);

  const handleCopy = () => {
    navigator.clipboard.writeText(pixCode);
    setCopied(true);
    toast({
      title: "Código copiado!",
      description: "O código PIX foi copiado para a área de transferência.",
    });
    
    setTimeout(() => setCopied(false), 2000);
  };

  return (
    <Card className="w-full max-w-md mx-auto">
      <CardHeader className="text-center">
        <CardTitle className="text-pix">Pagamento PIX</CardTitle>
        <CardDescription>
          Escaneie o QR Code ou copie o código para pagar
        </CardDescription>
      </CardHeader>
      <CardContent className="space-y-4">
        <div className="text-center font-bold text-xl">
          R$ {amount.toFixed(2)}
        </div>
        
        <div className="bg-gray-100 p-4 rounded-lg flex items-center justify-center">
          {/* This would typically be an actual QR code image */}
          <div className="w-48 h-48 bg-white p-2 rounded">
            <div className="w-full h-full border-2 border-gray-500 rounded flex items-center justify-center">
              <div className="text-sm text-center text-gray-500">
                QR Code PIX
                <div className="text-xs">(simulação)</div>
              </div>
            </div>
          </div>
        </div>

        <Separator />
        
        <div className="space-y-2">
          <p className="text-sm text-muted-foreground text-center">
            Ou copie o código PIX abaixo:
          </p>
          <div className="flex items-center">
            <div className="border rounded-l-md p-2 bg-gray-50 flex-1 font-mono text-sm overflow-hidden text-ellipsis">
              {pixCode}
            </div>
            <Button 
              variant="outline" 
              size="icon" 
              className="rounded-l-none"
              onClick={handleCopy}
            >
              {copied ? <Check className="h-4 w-4" /> : <Copy className="h-4 w-4" />}
            </Button>
          </div>
        </div>
      </CardContent>
      <CardFooter className="flex-col space-y-2">
        <p className="text-xs text-muted-foreground text-center w-full">
          O QR Code expira em 30 minutos. Após o pagamento, 
          pode levar alguns instantes para processarmos seu pagamento.
        </p>
        {onRefresh && (
          <Button 
            variant="outline" 
            className="w-full" 
            onClick={onRefresh}
            disabled={isLoading}
          >
            {isLoading ? (
              <>
                <RefreshCw className="mr-2 h-4 w-4 animate-spin" />
                Verificando...
              </>
            ) : (
              <>
                <RefreshCw className="mr-2 h-4 w-4" />
                Verificar Pagamento
              </>
            )}
          </Button>
        )}
      </CardFooter>
    </Card>
  );
};

export default PixQRCode;
