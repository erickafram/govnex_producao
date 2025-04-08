
import React from "react";
import { CNPJData } from "@/types";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Separator } from "@/components/ui/separator";

interface CNPJResultProps {
  data: CNPJData;
}

const CNPJResult: React.FC<CNPJResultProps> = ({ data }) => {
  return (
    <Card className="w-full max-w-2xl mx-auto">
      <CardHeader>
        <CardTitle className="text-xl">Resultado da Consulta</CardTitle>
      </CardHeader>
      <CardContent className="space-y-6">
        <div className="space-y-2">
          <h3 className="font-semibold text-lg">{data.razaoSocial}</h3>
          {data.nomeFantasia && (
            <p className="text-muted-foreground">{data.nomeFantasia}</p>
          )}
          <div className="flex flex-wrap gap-2 items-center">
            <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
              {data.cnpj}
            </span>
            <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${
              data.situacao === "ATIVA" 
                ? "bg-green-100 text-green-800"
                : "bg-red-100 text-red-800"
            }`}>
              {data.situacao}
            </span>
          </div>
        </div>

        <Separator />

        <div>
          <h4 className="font-medium mb-2">Atividade Principal</h4>
          <p className="text-sm">
            <span className="font-mono text-muted-foreground">{data.atividadePrincipal.codigo}</span> - {data.atividadePrincipal.descricao}
          </p>
        </div>

        <div>
          <h4 className="font-medium mb-2">Endereço</h4>
          <p className="text-sm">
            {data.endereco.logradouro}, {data.endereco.numero}
            {data.endereco.complemento && `, ${data.endereco.complemento}`}
            <br />
            {data.endereco.bairro}, {data.endereco.municipio} - {data.endereco.uf}
            <br />
            CEP: {data.endereco.cep}
          </p>
        </div>

        <div>
          <h4 className="font-medium mb-2">Capital Social</h4>
          <p className="text-sm">
            R$ {data.capitalSocial.toLocaleString('pt-BR', { minimumFractionDigits: 2 })}
          </p>
        </div>

        {data.socios && data.socios.length > 0 && (
          <div>
            <h4 className="font-medium mb-2">Sócios</h4>
            <ul className="space-y-2">
              {data.socios.map((socio, index) => (
                <li key={index} className="text-sm bg-gray-50 p-3 rounded">
                  <p className="font-medium">{socio.nome}</p>
                  <p className="text-xs text-muted-foreground">{socio.qualificacao}</p>
                </li>
              ))}
            </ul>
          </div>
        )}

        <div className="text-xs text-muted-foreground pt-2">
          <p>Data da Situação Cadastral: {data.dataSituacao}</p>
          <p className="mt-1">Consulta realizada em: {new Date().toLocaleString('pt-BR')}</p>
        </div>
      </CardContent>
    </Card>
  );
};

export default CNPJResult;
