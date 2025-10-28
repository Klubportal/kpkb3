# Icon Bibliotheken - Verwendungsanleitung

## 📦 Installierte Pakete

1. **Font Awesome** - 10.000+ Icons
2. **Lucide Icons** - 1.000+ minimalistischer Icons
3. **Phosphor Icons** - 3.000+ vielfältige Icons

---

## 🎨 Verwendung in Blade-Templates

### 1️⃣ Font Awesome

```html
<!-- Solid Icons -->
<i class="fas fa-heart"></i>
<i class="fas fa-users"></i>
<i class="fas fa-calendar"></i>
<i class="fas fa-chart-bar"></i>

<!-- Regular Icons -->
<i class="far fa-heart"></i>
<i class="far fa-user"></i>

<!-- Brands -->
<i class="fab fa-facebook"></i>
<i class="fab fa-twitter"></i>
<i class="fab fa-instagram"></i>

<!-- Mit Größe und Farbe -->
<i class="fas fa-heart text-red-500 text-3xl"></i>
<i class="fas fa-star text-yellow-400 text-xl"></i>
```

**Alle Icons:** https://fontawesome.com/icons

---

### 2️⃣ Lucide Icons

```html
<!-- Basis Verwendung -->
<i data-lucide="heart"></i>
<i data-lucide="users"></i>
<i data-lucide="calendar"></i>
<i data-lucide="bar-chart"></i>

<!-- Mit Tailwind Klassen -->
<i data-lucide="heart" class="w-6 h-6 text-red-500"></i>
<i data-lucide="star" class="w-8 h-8 text-yellow-400"></i>

<!-- Häufig verwendete Icons -->
<i data-lucide="home"></i>
<i data-lucide="mail"></i>
<i data-lucide="phone"></i>
<i data-lucide="search"></i>
<i data-lucide="menu"></i>
<i data-lucide="x"></i>
<i data-lucide="arrow-right"></i>
<i data-lucide="check"></i>
```

**Alle Icons:** https://lucide.dev/icons/

---

### 3️⃣ Phosphor Icons

```html
<!-- Bold Style -->
<i class="ph-bold ph-heart"></i>
<i class="ph-bold ph-users"></i>
<i class="ph-bold ph-calendar"></i>
<i class="ph-bold ph-chart-bar"></i>

<!-- Mit Größe und Farbe -->
<i class="ph-bold ph-heart text-red-500 text-3xl"></i>
<i class="ph-bold ph-star text-yellow-400 text-xl"></i>

<!-- Häufig verwendete Icons -->
<i class="ph-bold ph-house"></i>
<i class="ph-bold ph-envelope"></i>
<i class="ph-bold ph-phone"></i>
<i class="ph-bold ph-magnifying-glass"></i>
<i class="ph-bold ph-list"></i>
<i class="ph-bold ph-x"></i>
<i class="ph-bold ph-arrow-right"></i>
<i class="ph-bold ph-check"></i>
```

**Alle Icons:** https://phosphoricons.com/

---

## 🚀 Beispiele für deine Landing Page

### Feature Card mit Font Awesome

```html
<div class="glass-card rounded-3xl p-10">
    <div class="w-20 h-20 rounded-2xl flex items-center justify-center mb-8"
         style="background: linear-gradient(135deg, #dc262615, #991b1b15);">
        <i class="fas fa-users text-4xl text-red-600"></i>
    </div>
    <h3 class="text-2xl font-bold mb-4">Spielerverwaltung</h3>
    <p class="text-gray-600">Komplette Spielerdatenbank...</p>
</div>
```

### Feature Card mit Lucide

```html
<div class="glass-card rounded-3xl p-10">
    <div class="w-20 h-20 rounded-2xl flex items-center justify-center mb-8"
         style="background: linear-gradient(135deg, #dc262615, #991b1b15);">
        <i data-lucide="users" class="w-10 h-10 text-red-600"></i>
    </div>
    <h3 class="text-2xl font-bold mb-4">Spielerverwaltung</h3>
    <p class="text-gray-600">Komplette Spielerdatenbank...</p>
</div>
```

### Feature Card mit Phosphor

```html
<div class="glass-card rounded-3xl p-10">
    <div class="w-20 h-20 rounded-2xl flex items-center justify-center mb-8"
         style="background: linear-gradient(135deg, #dc262615, #991b1b15);">
        <i class="ph-bold ph-users text-5xl text-red-600"></i>
    </div>
    <h3 class="text-2xl font-bold mb-4">Spielerverwaltung</h3>
    <p class="text-gray-600">Komplette Spielerdatenbank...</p>
</div>
```

---

## 💡 Icon-Vorschläge für Features

### Spielerverwaltung
- Font Awesome: `fas fa-users`, `fas fa-user-friends`
- Lucide: `users`, `user-check`
- Phosphor: `ph-users`, `ph-user-circle`

### Spielplanung
- Font Awesome: `fas fa-calendar-alt`, `fas fa-clock`
- Lucide: `calendar`, `calendar-check`
- Phosphor: `ph-calendar`, `ph-calendar-check`

### Website
- Font Awesome: `fas fa-globe`, `fas fa-desktop`
- Lucide: `globe`, `layout`
- Phosphor: `ph-globe`, `ph-layout`

### Analytics
- Font Awesome: `fas fa-chart-bar`, `fas fa-chart-line`
- Lucide: `bar-chart`, `trending-up`
- Phosphor: `ph-chart-bar`, `ph-chart-line`

### Finanzen
- Font Awesome: `fas fa-dollar-sign`, `fas fa-wallet`
- Lucide: `dollar-sign`, `wallet`
- Phosphor: `ph-currency-dollar`, `ph-wallet`

### Mobile App
- Font Awesome: `fas fa-mobile-alt`, `fas fa-tablet-alt`
- Lucide: `smartphone`, `tablet`
- Phosphor: `ph-device-mobile`, `ph-devices`

---

## 🎯 Best Practices

1. **Konsistenz**: Verwende eine Icon-Bibliothek durchgängig im gesamten Projekt
2. **Größe**: Nutze Tailwind-Klassen für responsive Größen (`text-xl`, `text-2xl`, etc.)
3. **Farbe**: Verwende deine Primary/Secondary Farben aus dem Backend
4. **Spacing**: Achte auf ausreichend Abstand um Icons (`mb-4`, `mr-2`, etc.)
5. **Accessibility**: Füge `aria-label` hinzu für Screen Reader

---

## 🔄 Nach Änderungen

Wenn du Icons änderst oder neue hinzufügst:

```bash
npm run build
php artisan view:clear
```

---

## 📚 Weitere Ressourcen

- Font Awesome: https://fontawesome.com/docs
- Lucide: https://lucide.dev/guide/
- Phosphor: https://phosphoricons.com/
