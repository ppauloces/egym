# 🚀 PWA - Configuração Completa do E-GYM

## ✅ O que já está configurado:

1. ✅ Plugin PWA instalado (`vite-plugin-pwa`)
2. ✅ Configuração no `vite.config.js`
3. ✅ Composable `usePwaInstall.ts` criado
4. ✅ Componente `PwaInstallDialog.vue` criado
5. ✅ Integração no `GestorLayout.vue`

---

## 📋 Próximos passos (IMPORTANTE):

### 1️⃣ Criar os ícones PWA

Você precisa criar 2 ícones na pasta `public/`:

- **`pwa-192x192.png`** (192x192 pixels)
- **`pwa-512x512.png`** (512x512 pixels)

#### Como criar os ícones:

**Opção A - Online (Recomendado):**
1. Acesse: https://www.pwabuilder.com/imageGenerator
2. Faça upload do logo da academia
3. Baixe os ícones gerados
4. Renomeie para `pwa-192x192.png` e `pwa-512x512.png`
5. Coloque em `public/`

**Opção B - Usando a logo existente:**
```bash
# Se tiver ImageMagick instalado:
magick convert public/images/logo.png -resize 192x192 public/pwa-192x192.png
magick convert public/images/logo.png -resize 512x512 public/pwa-512x512.png
```

**Opção C - Usar Figma/Photoshop:**
- Redimensione o logo para 192x192 e 512x512
- Exporte como PNG
- Salve em `public/`

---

### 2️⃣ Rebuild do Vite

Após criar os ícones:

```bash
npm run build
```

Ou se estiver em desenvolvimento:

```bash
npm run dev
```

---

## 🎯 Como funciona:

### **Android (Chrome/Edge):**
1. Usuário entra no E-GYM
2. Após 3 segundos, aparece um dialog elegante
3. Botão "Instalar" → instalação automática
4. App é adicionado à tela inicial

### **iOS (Safari):**
1. Usuário entra no E-GYM
2. Após 3 segundos, aparece um dialog com instruções
3. Passo a passo visual:
   - Compartilhar → Adicionar à Tela de Início
4. Usuário clica "Entendi"

### **Lógica inteligente:**
- ✅ Não exibe se já estiver instalado
- ✅ Não exibe se o usuário já recusou nos últimos 7 dias
- ✅ Aguarda 3 segundos antes de exibir
- ✅ Detecta automaticamente Android/iOS

---

## 🧪 Como testar:

### **No Desktop (Chrome):**
1. Abra o DevTools (F12)
2. Vá em "Application" → "Manifest"
3. Verifique se o manifesto está correto
4. Clique em "Application" → "Service Workers"
5. Teste a instalação

### **No Android:**
1. Abra o Chrome
2. Acesse o E-GYM
3. Aguarde o dialog aparecer
4. Clique em "Instalar"
5. Verifique o ícone na tela inicial

### **No iOS:**
1. Abra o Safari
2. Acesse o E-GYM
3. Aguarde o dialog aparecer
4. Siga as instruções
5. Verifique o ícone na tela inicial

---

## 🎨 Personalização:

### Cores do app:
Já configuradas no `vite.config.js`:
- `theme_color`: `#1EB4F0` (azul do E-GYM)
- `background_color`: `#FFFFFF`

### Alterar o tempo de espera:
Em `PwaInstallDialog.vue`, linha 24:
```ts
setTimeout(() => {
  // ...
}, 3000) // ← Altere aqui (em milissegundos)
```

### Alterar os dias para reexibir:
Em `usePwaInstall.ts`, linha 97:
```ts
if (daysSinceDismissed < 7) { // ← Altere aqui
```

---

## 🐛 Troubleshooting:

### "Os ícones não aparecem"
- Certifique-se de que `pwa-192x192.png` e `pwa-512x512.png` existem em `public/`
- Execute `npm run build` novamente
- Limpe o cache do navegador (Ctrl+F5)

### "O dialog não aparece"
- Verifique o console do navegador
- Teste em uma janela anônima
- Limpe o localStorage: `localStorage.removeItem('pwa-install-dismissed')`

### "Erro ao instalar no Android"
- Verifique se está usando HTTPS (ou localhost)
- Verifique o manifesto no DevTools
- Veja se o Service Worker está ativo

---

## 📚 Recursos úteis:

- [PWA Builder](https://www.pwabuilder.com/)
- [Vite PWA Plugin](https://vite-pwa-org.netlify.app/)
- [Web.dev PWA](https://web.dev/progressive-web-apps/)

---

## ✨ Funcionalidades PWA implementadas:

- ✅ Instalação automática (Android)
- ✅ Instruções visuais (iOS)
- ✅ Offline support (Service Worker)
- ✅ Cache inteligente
- ✅ Atualização automática
- ✅ Modo standalone
- ✅ Ícones adaptativos
- ✅ Theme color
- ✅ Splash screen

---

**🎉 Pronto! O E-GYM agora é um PWA completo!**
