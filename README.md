# INDUX - Monitoramento Industrial

Sistema PHP simples para cadastrar equipamentos industriais, registrar leituras de sensores, acompanhar alarmes e controlar usuarios.

## Estrutura dos arquivos

| Arquivo | Responsabilidade |
| --- | --- |
| `init.php` | Inicia a sessao, define constantes do sistema e concentra funcoes de permissao, labels e calculos visuais. |
| `db.php` | Centraliza a conexao PDO com o MySQL e funcoes de consulta usadas pelas telas. |
| `login.php` | Faz autenticacao do usuario e grava os dados principais na sessao. |
| `header.php` | Menu lateral, usuario logado e badge de alarmes criticos. |
| `footer.php` | Rodape com contadores resumidos de equipamentos e alarmes. |
| `dashboard.php` | Visao geral: KPIs, ultimas leituras, alarmes ativos e equipamentos recentes. |
| `equipamentos.php` | Lista, busca, filtra, altera status e exclui equipamentos. |
| `novo-equipamento.php` | Formulario de criacao e edicao de equipamentos. |
| `monitoramento.php` | Mostra leituras por equipamento e permite registrar novas leituras manualmente. |
| `alarmes.php` | Lista alarmes, filtra por severidade/status e permite marcar como resolvido. |
| `usuarios.php` | CRUD de usuarios, perfis e permissoes. |
| `styles.css` | Estilos visuais do sistema. |
| `banco.sql` | Script de criacao do banco e dados iniciais. |
| `banco(backup).sql` | Script de criacao do banco e dados para recuperação. |

## Fluxo principal

1. O usuario entra por `login.php`.
2. `init.php` inicia a sessao e disponibiliza funcoes como `requerLogin()`, `ehAdmin()` e `podeResolverAlarme()`.
3. As paginas chamam `db.php` para buscar ou salvar dados no MySQL.
4. Equipamentos sao cadastrados em `novo-equipamento.php` e listados em `equipamentos.php`.
5. Leituras sao registradas em `monitoramento.php`; se uma leitura fica fora dos limites minimo ou maximo, o sistema cria alarmes.
6. Alarmes aparecem em `alarmes.php` e nos resumos do dashboard, header e footer.

## Convencao de nomes usada

Os nomes de variaveis foram ajustados para ficarem mais claros para um programador intermediario:

- Variaveis de entidade usam o nome do dominio: `$equipamento`, `$usuarioItem`, `$alarme`, `$leitura`.
- IDs deixam claro de qual entidade sao: `$equipamentoId`, `$alarmeId`, `$usuarioPostId`.
- Consultas SQL usam prefixo `consulta`: `$consultaEquipamento`, `$consultaAlarmes`, `$consultaLeituras`.
- Arrays de filtros usam nomes explicitos: `$filtrosSql`, `$parametros`, `$buscaLike`.
- Valores calculados indicam o que representam: `$classeTemperatura`, `$percentualPressao`, `$contagens`.

## Banco de dados

O banco padrao definido em `db.php` e `db_teste`. Para recriar as tabelas, use o arquivo `banco.sql` no MySQL. As tabelas principais sao:

- `usuarios`
- `equipamentos`
- `leituras_sensor`
- `alarmes`
- `log_sistema`

## Observacoes importantes

- Nomes de campos do banco e dos formularios foram mantidos, porque eles precisam bater com o SQL e com os `name=""` dos inputs.
- A logica de permissao fica em `init.php`; antes de criar uma regra nova, confira se ja existe uma funcao pronta.
- O projeto usa PHP com PDO. Evite montar SQL com dados do usuario direto na string; prefira `prepare()` e `execute()`.
