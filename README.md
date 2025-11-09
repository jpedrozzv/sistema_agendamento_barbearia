# Sistema de Agendamento da Barbearia

Este projeto fornece um painel administrativo e uma área do cliente para agendar atendimentos na barbearia. O sistema é construído em **PHP 8**, **MySQL** e **Bootstrap 5**.

## Pré-requisitos

- PHP 8.1+
- MySQL 5.7+ ou MariaDB equivalente
- Extensão `mysqli`
- Composer (opcional, caso queira adicionar dependências no futuro)

## Configuração

1. Copie o arquivo de exemplo de variáveis de ambiente:
   ```bash
   cp .env.example .env
   ```
2. Ajuste os valores de conexão no `.env` conforme seu ambiente local.
3. Importe o schema inicial do banco ou crie as tabelas necessárias.
4. Configure um host virtual apontando para a raiz do projeto ou utilize um servidor embutido do PHP:
   ```bash
   php -S 127.0.0.1:8000
   ```

## Estrutura sugerida

- `src/` contém funções reutilizáveis (ex.: cálculo de horários disponíveis).
- `tests/` armazena testes de unidade simples que podem ser executados via `php tests/arquivo_test.php`.
- Páginas públicas e administrativas permanecem na raiz para compatibilidade com o projeto original.

## Testes

Para validar a lógica de horários disponíveis:
```bash
php tests/horarios_dispo_test.php
```

## Boas práticas adotadas

- Consultas SQL com **prepared statements** para evitar SQL Injection.
- Sanitização de entradas de formulários sensíveis (cadastro, agendamentos, etc.).
- Modal de confirmação reutilizável em `footer.php`, evitando código JavaScript duplicado.
- Lógica compartilhada de disponibilidade em `src/horarios.php`, reaproveitada por páginas e testes.

## Próximos passos sugeridos

- Criar migrations e seeds automatizados para o banco.
- Adicionar testes de integração para cadastros e autenticação.
- Modularizar páginas em componentes menores conforme o projeto evoluir.
