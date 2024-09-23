# CRUD - Instruções de Instalação

## 1. Criação do Diretório
Crie um diretório chamado **CRUD** no seu servidor web e mova todos os arquivos do projeto para esse diretório.

## 2. Configuração do Banco de Dados
- Execute o arquivo **tabela.sql** no seu banco de dados para criar as tabelas necessárias.
- No exemplo, o banco de dados utilizado é chamado **crud_int**. Se você usar um nome diferente, será necessário atualizar a variável **$db** no arquivo **crud.php** com o nome correto do seu banco de dados.

## 3. Pré-Requisitos
O sistema inclui um exemplo de upload de imagens no cadastro. Para garantir que essa funcionalidade funcione corretamente:
- Crie um diretório chamado **imagens** dentro do diretório **CRUD**.
- Conceda permissões de escrita a esse diretório.

**Observação:** Verifique se as configurações do seu PHP não estão bloqueando o envio de imagens.

## 4. Acesso ao Sistema
Após a instalação e inicialização do seu servidor, acesse o sistema pelo seguinte endereço no navegador:
