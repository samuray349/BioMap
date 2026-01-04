# BioMap

## Descrição
Disponibilizar uma plataforma que aumenta a visibilidade de instituições de conservação animal e que permite aos utilizadores alertar a aparência de um animal, tal como pode informar-se acerca do mesmo.

## Funcionalidades principais
- Mapa com pontos de interesse: o mapa terá as localizações de instituições e animais, a localização dos animais poderá ser feita pelos utilizadores, ao clicar num animal ou instituição poderá ver diversas informações acerca do mesmo
- Registo de animais: Um administrador irá poder criar, actualizar e apagar registos de animais, cada registo vai ter dados como nome, descrição, características físicas, risco de extinção
- Página detalhada: página para cada animal onde será possível aceder aos diversos dados de cada animal
- Doações: o utilizador vai ter a opção de utilizar a plataforma como centro de doações para várias instituições parceiras

## Tecnologias utilizadas
- JS
- PostGreSQL
- Node.JS
- PHP
- HTML5/CSS

## API Switch

O BioMap suporta uma mundaça entre duas APIs backend: **Node.js** (Vercel) e **PHP** (Railway). Esta funcionalidade permite aos utilizadores escolherem qual API utilizar através de um toggle no header da aplicação.

### Como funciona

A aplicação inclui um sistema de configuração que permite alternar entre as duas APIs:
- **Node.js API**: Hospedada no Vercel, utiliza Express.js e serve como API principal
- **PHP API**: Hospedada no Railway, utiliza PHP Slim Framework como alternativa

A preferência do utilizador é guardada no `localStorage` do navegador e persiste entre páginas. O toggle está disponível no header de todas as páginas da aplicação.

### Configuração das APIs

| Propriedade | Node.js API | PHP API |
|------------|-------------|---------|
| **Hosting** | Vercel | Railway |
| **URL Local** | `http://localhost:3000` | `https://biomap-production.up.railway.app` |
| **URL Produção** | `https://bio-map-xi.vercel.app` | `https://biomap-production.up.railway.app` |
| **Framework** | Express.js (Node.js) | Slim Framework (PHP) |
| **Endpoints Password Reset** | Sempre utilizado | Não suportado (usa Node.js) |

### Endpoints especiais

Alguns endpoints têm comportamento especial:
- **Password Reset** (`api/forgot-password`, `api/reset-password`): Sempre utilizam a API Node.js, independentemente da selecção do utilizador
- **Mapeamento de Endpoints**: A API PHP utiliza um sistema de mapeamento que converte endpoints do formato Node.js para ficheiros PHP (ex: `users/:id` → `users/get.php?id=123`)


### Mapeamento Completo de Endpoints

A aplicação mapeia automaticamente os endpoints Node.js para os equivalentes PHP quando a API PHP está selecionada. A tabela abaixo apresenta todos os endpoints disponíveis:

#### Autenticação

| Método | Node.js Endpoint | PHP Endpoint | Notas |
|--------|------------------|--------------|-------|
| POST | `api/login` | `api/login` | Router PHP |
| POST | `api/signup` | `api/signup` | Router PHP |
| POST | `api/check-user` | `api/check-user` | Router PHP |
| POST | `api/forgot-password` | - | **Sempre Node.js** (não suportado em PHP) |
| POST | `api/reset-password` | - | **Sempre Node.js** (não suportado em PHP) |

#### Utilizadores (Users)

| Método | Node.js Endpoint | PHP Endpoint | Notas |
|--------|------------------|--------------|-------|
| GET | `users` | `users/list.php` | Lista utilizadores |
| GET | `users/list.php` | `users/list.php` | Acesso directo |
| GET | `users/estados` | `users/estados.php` | Estados dos utilizadores |
| GET | `users/estatutos` | `users/estatutos.php` | Estatutos dos utilizadores |
| GET | `users/:id` | `users/get.php?id={id}` | Obter utilizador por ID |
| PUT | `users/:id` | `users/update.php` | Actualizar utilizador (router PHP) |
| PUT | `users/:id/password` | `users/update_password.php` | Actualizar password |
| PUT | `users/:id/funcao` | `users/update_funcao.php` | Actualizar função |
| PUT | `users/:id/estado` | `users/update_estado.php` | Actualizar estado |
| POST | `users` | `users` | Criar utilizador (router PHP) |
| DELETE | `users/:id` | `users/delete.php` | Eliminar utilizador (router PHP) |

#### Animais

| Método | Node.js Endpoint | PHP Endpoint | Notas |
|--------|------------------|--------------|-------|
| GET | `animais` | `animais/list.php` | Lista animais |
| GET | `animais/list.php` | `animais/list.php` | Acesso directo |
| GET | `animais/familias` | `animais/familias.php` | Lista famílias |
| GET | `animais/estados` | `animais/estados.php` | Estados de conservação |
| GET | `animaisDesc/:id` | `animais/get.php?id={id}` | Obter animal por ID (descrição completa) |
| POST | `animais` | `animais` | Criar animal (router PHP) |
| PUT | `animais/:id` | `animais/update.php` | Actualizar animal (router PHP) |
| DELETE | `animais/:id` | `animais/delete.php` | Eliminar animal (router PHP) |

#### Alertas (Avistamentos)

| Método | Node.js Endpoint | PHP Endpoint | Notas |
|--------|------------------|--------------|-------|
| GET | `api/alerts` | `api/alerts` | Lista alertas (router PHP) |
| GET | `alerts/list.php` | `alerts/list.php` | Acesso directo |
| POST | `api/alerts` | `api/alerts` | Criar alerta (router PHP) |
| DELETE | `api/alerts/:id` | `alerts/delete.php` | Eliminar alerta (router PHP) |

#### Instituições

| Método | Node.js Endpoint | PHP Endpoint | Notas |
|--------|------------------|--------------|-------|
| GET | `instituicoes` | `instituicoes/list.php` | Lista instituições |
| GET | `instituicoes/list.php` | `instituicoes/list.php` | Acesso directo |
| GET | `instituicoesDesc/:id` | `instituicoes/get.php?id={id}` | Obter instituição por ID (descrição completa) |
| POST | `instituicoes` | `instituicoes` | Criar instituição (router PHP) |
| PUT | `instituicoes/:id` | `instituicoes/update.php` | Actualizar instituição (router PHP) |
| DELETE | `instituicoes/:id` | `instituicoes/delete.php` | Eliminar instituição (router PHP) |

#### Outros

| Método | Node.js Endpoint | PHP Endpoint | Notas |
|--------|------------------|--------------|-------|
| GET | `health` | `health.php` | Health check |

**Notas importantes:**
- Endpoints marcados como "Router PHP" são geridos pelo router PHP (index.php) que direcciona com base no método HTTP
- Endpoints com `:id` são rotas parametrizadas onde o ID é substituído pelo valor real (ex: `users/123`)
- Para pedidos GET com `:id`, o PHP utiliza parâmetros de query (ex: `users/get.php?id=123`)
- Para pedidos PUT/DELETE com `:id`, o PHP utiliza a estrutura de path (ex: `users/123`) que é gerida pelo router
- Os endpoints `api/forgot-password` e `api/reset-password` **sempre** utilizam a API Node.js, independentemente da selecção

## Site do Projeto
[Visite o site do projeto](https://lucped.antrob.eu/public/)

## Site do modelo da base de dados
[Visite o site do modelo](https://dbdiagram.io/d/BioMap-694b0774dbf05578e6697a86)

## Autor
Pedro Oliveira
Lucas Reis
