# MenteSerena

## Descrição do Projeto

O MenteSerena é um sistema desenvolvido para gerenciar o cadastro e acompanhamento de pacientes, alunos, professores e sessões de terapia. O objetivo principal é facilitar a administração de informações e melhorar a comunicação entre os envolvidos no processo terapêutico.

## Funcionalidades

- **Cadastro de Pacientes**: Permite o registro de novos pacientes, incluindo informações pessoais, contato de emergência, endereço, escolaridade e necessidades especiais.
- **Cadastro de Alunos**: Permite o registro de novos alunos, incluindo informações pessoais, formação, especialidade e dados de contato.
- **Cadastro de Professores**: Permite o registro de novos professores, incluindo informações pessoais, formação, especialidade e dados de contato.
- **Cadastro de Sessões**: Permite o registro de sessões de terapia, incluindo o paciente, professor, data, registro da sessão e anotações.
- **Gerenciamento de Documentos**: Permite o upload e gerenciamento de documentos relacionados às sessões e pacientes.
- **Relatórios**: Geração de relatórios detalhados sobre sessões, pacientes e outros dados relevantes.

## Regras de Negócio

1. **Validação de CPF**: Todos os CPFs devem ser válidos e seguir o formato `000.000.000-00`.
2. **Validação de Data**: Datas de nascimento, contratação e sessões devem ser válidas e não podem ser futuras.
3. **Cadastro de Sessões**: Sessões podem ser salvas como rascunho e devem ser finalizadas dentro de 48 horas.
4. **Associação de Pacientes e Alunos**: Pacientes devem estar associados a alunos para que possam ser cadastradas sessões.
5. **Níveis de Acesso**:
    - **Administrador**: Acesso completo a todas as funcionalidades.
    - **Professor**: Acesso a informações de pacientes e sessões.
    - **Aluno**: Acesso restrito a informações de seus pacientes e sessões.

## Tecnologias Utilizadas

- **Frontend**: HTML, CSS, JavaScript, jQuery
- **Backend**: PHP, MySQL

## Como Executar o Projeto

1. Clone o repositório:
    ```bash
    git clone https://github.com/seu-usuario/MenteSerena.git
    ```
2. Configure o banco de dados MySQL com o script fornecido em `database.sql`.
3. Configure o arquivo `config.php` com as credenciais do banco de dados.
4. Inicie o servidor local:
    ```bash
    php -S localhost:8000
    ```
5. Acesse o sistema em `http://localhost:8000`.

## Contribuição

1. Faça um fork do projeto.
2. Crie uma branch para sua feature:
    ```bash
    git checkout -b minha-feature
    ```
3. Commit suas mudanças:
    ```bash
    git commit -m 'Minha nova feature'
    ```
4. Envie para o repositório remoto:
    ```bash
    git push origin minha-feature
    ```
5. Abra um Pull Request.

## Licença

Este projeto está licenciado sob a Licença MIT. Veja o arquivo [LICENSE](LICENSE) para mais detalhes.
