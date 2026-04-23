🧠 SYSTEM CONTEXT

Stack oficial:

Backend: Laravel (PHP 8.2+)
Frontend: Next.js (React 18+)
Database: PostgreSQL
Cache/Queue: Redis
Infra: Docker + Nginx

Arquitetura: API-first + SSR frontend

🤖 AGENT SYSTEM (MULTI-AGENTS)
Papéis
planner → analisa e divide tarefas
coder → implementa código
reviewer → valida qualidade e segurança
tester → executa testes/build
fixer → corrige falhas automaticamente
🔁 EXECUTION FLOW
planner → coder → reviewer → tester → fixer → loop (max 3x)

Se falhar 3x → escalar para humano

📁 PROJECT STRUCTURE
/backend      → Laravel
/frontend     → Next.js
/docker       → infra
/database     → migrations/scripts
/.agents      → memória dos agentes
⚡ COMMANDS (OBRIGATÓRIO)
🐳 Docker
docker-compose up -d --build
docker-compose down
🔙 Backend (Laravel)
cd backend

composer install

php artisan serve
php artisan migrate
php artisan test

# qualidade
vendor/bin/phpcs
⚛️ Frontend (Next.js)
cd frontend

npm install
npm run dev
npm run build
npm run lint
🏗️ BACKEND RULES (LARAVEL)
Estrutura obrigatória
Controllers → apenas entrada/saída
Business logic → Services / Actions
Models → Eloquent
Validação → Form Requests
Boas práticas
Usar:
Eloquent ORM
Resource classes (API)
UUID como PK (preferencial)
❌ Proibido
SQL direto em controller
lógica de negócio no controller
acessar Request sem validação
⚛️ FRONTEND RULES (NEXT.JS)
Padrões
App Router (Next 13+)
Server Components por padrão
Client Components apenas quando necessário
Estrutura
/components → UI
/lib        → API/services
/hooks      → lógica
❌ Proibido
fetch direto espalhado
lógica dentro de componentes grandes
estado global desnecessário
🐘 DATABASE RULES (POSTGRES)
snake_case
migrations obrigatórias
índices para performance
❌ Proibido
alterar tabela manualmente em produção
queries sem índice em dados grandes
🔐 SECURITY (OBRIGATÓRIO)
Validação com FormRequest
Sanitização de saída
Proteção:
SQL Injection
XSS
CSRF
Nunca permitir:
.env no repositório
credenciais hardcoded
debug ativo em produção
📦 REDIS RULES

Usar para:

cache
filas
rate limiting
🔍 CODE QUALITY
DRY
KISS
SOLID
Limites
função: máx 50 linhas
arquivo: manter coeso
⚡ PERFORMANCE
evitar N+1 queries
usar eager loading (with())
cache quando necessário
🔁 CHANGE POLICY
Pequenas mudanças

→ aplicar direto

Grandes mudanças
Planejar
Listar impacto
Pedir confirmação
🚨 FAILURE PROTOCOL
Identificar causa raiz
Corrigir corretamente
Reexecutar pipeline

Após 3 falhas:

→ parar
→ registrar em .agents/errors.md

🧪 TEST POLICY
Backend
php artisan test
Frontend
npm run test

Regras:

sempre atualizar testes
nunca ignorar falhas
🧾 COMMIT PADRÃO
feat: nova feature
fix: correção
refactor: melhoria interna
perf: otimização
🤖 AGENT EXECUTION PROTOCOL
Antes
ler contexto
analisar impacto
identificar arquivos
Durante
alterar o mínimo possível
manter padrão existente
Depois
rodar:
build
testes
lint
🧠 AGENT MEMORY (.agents)
.agents/
  context.md
  decisions.md
  tasks.md
  errors.md
Obrigatório:
registrar decisões
registrar erros
manter contexto atualizado
📊 OBSERVABILITY
logs estruturados
erros rastreáveis
sem dados sensíveis
🔐 DEPENDENCY POLICY
evitar novas libs
se necessário:
justificar
validar compatibilidade
🔚 FINAL CHECKLIST

Antes de finalizar qualquer tarefa:

 Build OK
 Testes OK
 Lint OK
 Segurança validada
 Sem código morto
🔥 AUTONOMOUS MODE

Agente pode agir sem confirmação se:

mudança pequena
reversível
sem impacto estrutural

Caso contrário:

→ pedir aprovação

🚀 READY FOR
produção
CI/CD
microserviços
scaling horizontal