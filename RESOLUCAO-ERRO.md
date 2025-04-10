# Resolução do Erro de Build do Docker

O erro que ocorreu durante o build do frontend está relacionado ao Vite tentando carregar o diretório `/app/src/components/ui` como se fosse um arquivo. Isso geralmente acontece por um problema em algum import no código.

## ✅ Solução Recomendada (Nova)

Foi criada uma solução automatizada que resolve o problema:

1. Primeiro, dê permissão de execução aos scripts:
```bash
chmod +x pre-build.sh start-production-docker.sh
```

2. Execute o script de produção:
```bash
./start-production-docker.sh
```

Este script irá:
- Executar o `pre-build.sh` para criar um arquivo index.js na pasta components/ui
- Parar containers anteriores
- Reconstruir todas as imagens
- Iniciar a aplicação em modo detached

## Detalhes da Solução

O problema ocorre porque o Vite tenta carregar o diretório `src/components/ui` como um módulo, mas não encontra um arquivo de entrada (como index.js).

A solução consiste em:
1. Criar um arquivo `src/components/ui/index.js` que exporta os componentes necessários
2. Modificar o Dockerfile para garantir que este arquivo existe durante o build
3. Usar um script de pré-build para identificar e corrigir possíveis problemas de importação

## Detalhes Técnicos

O erro específico:
```
[vite:load-fallback] Could not load /app/src/components/ui: EISDIR: illegal operation on a directory, read
```

Acontece quando:
1. Existe algum import no código que referencia o diretório ui diretamente, como:
```javascript
import UI from "./components/ui"; // Incorreto
```

2. O Vite tenta resolver esse módulo, mas não encontra um arquivo index.js para processar.

## Se o Problema Persistir

Se mesmo com essas soluções o problema persistir, você pode:

1. Verificar todos os imports em seu código fonte que possam estar referenciando o diretório ui diretamente:
```bash
grep -r "from ['\"].*components\/ui['\"];" src/
```

2. Corrigir esses imports para referenciarem componentes específicos:
```javascript
// Em vez de:
import UI from "./components/ui";

// Use:
import { Button, Card } from "./components/ui";
// ou
import { Button } from "./components/ui/button";
import { Card } from "./components/ui/card";
```

3. Se precisar debugar mais a fundo:
```bash
docker-compose -f docker-compose.prod.yml build --no-cache frontend
docker-compose -f docker-compose.prod.yml logs -f frontend
``` 