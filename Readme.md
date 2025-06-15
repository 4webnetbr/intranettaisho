# Intranet Taisho

Sistema interno desenvolvido para centralizar processos, controle de estoque e rotinas administrativas internas da rede Taisho.

> Este repositório é utilizado exclusivamente como **repositório de código-fonte e controle de versão** (backup). O código **não é utilizado para deploy automático**.

---

## ⚙️ Tecnologias utilizadas

- PHP 8+
- CodeIgniter 4
- HTML5 + CSS3
- Bootstrap
- JavaScript (jQuery)
- MariaDB / MySQL
- Git + GitLens (VSCode)

---

## 📁 Estrutura principal do projeto

app/ # Controladores, Models e Views
public/ # Front controller (index.php) e assets públicos
docs/ # Documentação auxiliar
tests/ # Scripts de testes
writable/ # Diretórios temporários (cache, logs, sessões, uploads)

yaml
Copiar
Editar

---

## 💾 Uso do Git

O Git é utilizado como:

- **Backup** regular das alterações
- Histórico de versões do projeto
- Comparação de mudanças com auxílio do **GitLens**

Todos os commits são feitos diretamente na branch `main`.

---

## 🏷️ Versionamento

Tags são utilizadas para marcar versões ou estados importantes do sistema:

```bash
git tag -a v2025.06.15 -m "Backup com estrutura atualizada da Intranet"
git push origin v2025.06.15

🔒 Observação
Este repositório é privado. Não contém credenciais ou dados sensíveis.

👨‍💻 Autor
Douglas Ferreira
Analista de Sistemas – 4webnetbr
github.com/4webnetbr




