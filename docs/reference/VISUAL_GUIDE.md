# 🎬 Visual Guide - Club Portal Modern UI

## Page Layouts & Components Guide

---

## 📐 Standard Page Structure

Every page follows this responsive layout:

```
┌─────────────────────────────────────────────┐
│        GRADIENT HEADER with Icon            │  ← Unique color per page
│        Page Title & Description             │     (Height: 120px, Padding: 32px)
└─────────────────────────────────────────────┘

┌─────────┬─────────┬──────────┬──────────┐   ← Responsive Grid
│ Stat    │ Stat    │ Stat     │ Stat     │   │  (1 col mobile, 4 cols desktop)
│ Card 1  │ Card 2  │ Card 3   │ Card 4   │   │  (Gap: 24px)
└─────────┴─────────┴──────────┴──────────┘   ←

                [MAIN CONTENT AREA]           ← Tables, Forms, Cards
             (Padding: 24px top/bottom)
                 
┌──────────────────────────────────────────┐   ← Tips/Info Box
│  💡 Pro Tips / Information               │   (Optional, Bottom)
│  Professional advice or best practices   │
└──────────────────────────────────────────┘
```

---

## 🎨 Component Specifications

### 1. Portal Header
**Purpose**: Page title, introduction, primary action  
**Dimensions**: Full width, ~120px height  
**Features**:
- Gradient background (unique per page)
- Large title (H1)
- Subtitle text (optional)
- Action button (primary)
- Smooth animation on load

**Example**:
```
┌─────────────────────────────────────────┐
│ 🏢 Club Portal Dashboard                │  ← H1 (2.25rem, bold)
│ Manage your club's settings and more     │  ← Subtitle (gray)
│                              [+ NEW BTN] │  ← Primary button right
└─────────────────────────────────────────┘
```

---

### 2. Stat Cards
**Purpose**: Key metrics display  
**Dimensions**: ~250px wide, auto height  
**Layout**: 4 columns (desktop), 2 (tablet), 1 (mobile)  
**Features**:
- Colored top border (4px)
- Label (uppercase, small)
- Large value (2.5rem, bold)
- Subtitle/change percentage
- Hover: Lift + glow effect

**Example**:
```
┌─────────────────┐
│ ■ (color)       │  ← Top border (4px)
│ 💼 SPONSORS     │  ← Label (uppercase)
│ 24              │  ← Value (2.5rem)
│ Active orgs     │  ← Subtitle
└─────────────────┘
```

**Color Coding**:
- Card 1: Blue (Primary)
- Card 2: Green (Secondary)
- Card 3: Orange (Accent)
- Card 4: Purple (Highlight)

---

### 3. Action Cards
**Purpose**: Quick navigation & actions  
**Dimensions**: Flexible, ~280px per card  
**Layout**: 3 columns (desktop), 2 (tablet), 1 (mobile)  
**Features**:
- Left icon circle (gradient background)
- Title + description on right
- Hover: Color shift, border highlight
- Click: Ripple animation

**Example**:
```
┌─────────────────────────────────────────┐
│ ┌─────────┐                             │
│ │  💰    │ Manage Sponsors              │
│ │ ▓▓▓▓   │ Add and manage club sponsors │
│ └─────────┘                             │
└─────────────────────────────────────────┘
           ↓ Hover: Border glows, slides right
```

---

### 4. Tables
**Purpose**: Data display & management  
**Features**:
- Sticky header with gray background
- Row hover: Background color change + subtle shadow
- Staggered animation on load
- Responsive: Horizontal scroll on mobile
- Action buttons on hover (fade in)

**Example**:
```
┌─────────────────────────────────────────┐
│ Name              │ Status  │ Actions    │  ← Header (bg-gray)
├─────────────────────────────────────────┤
│ Nike Sports       │ ✓ Active│ Edit Remove│  ← Row 1
├─────────────────────────────────────────┤
│ Adidas Germany    │ ✓ Active│ Edit Remove│  ← Row 2
├─────────────────────────────────────────┤
│ Coca-Cola EUR     │ ⚠ Soon │ Edit Remove│  ← Row 3
└─────────────────────────────────────────┘
       ↓ Hover: Row lifts, shadow expands
```

---

### 5. Badges
**Purpose**: Status indicators  
**Types**: Success (green), Warning (orange), Danger (red), Info (blue)  

**Example**:
```
[✓ Active]     ← Green badge
[⚠ Warning]    ← Orange badge
[✗ Expired]    ← Red badge
[ℹ Info]       ← Blue badge
```

---

### 6. Forms
**Purpose**: Data input  
**Layout**: Vertical stacking, grid on desktop  
**Features**:
- Clean borders (1px gray)
- Focus: Lift effect, blue shadow, border color change
- Placeholder: Professional font
- Textarea: Monospace font for code/long text
- Select: Custom styling

**Example**:
```
┌─────────────────────────────────────────┐
│ Label *                                 │
│ ┌───────────────────────────────────┐   │
│ │ Input field                        │   │
│ │ Text enters here...                │   │  ← Focus: Lifts, shadow
│ └───────────────────────────────────┘   │
│ Hint text (optional)                    │
└─────────────────────────────────────────┘
```

---

### 7. Buttons
**Purpose**: Actions & submissions  
**Types**: Primary (blue), Secondary (gray), Danger (red)  
**Features**:
- Smooth hover effects
- Ripple animation on click
- Disabled state (opacity reduced)

**Example**:
```
Primary:     [🚀 Save Changes]    ← Blue gradient
Secondary:   [Cancel]             ← Gray
Danger:      [🗑️ Delete]          ← Red
```

---

### 8. Info Box (Tips)
**Purpose**: Professional guidance  
**Features**:
- Gradient background (light blue)
- Icon on left
- Title + description
- Bottom of page

**Example**:
```
┌─────────────────────────────────────────┐
│ 💡 Pro Tips for Social Media Growth     │
│    Post consistently, engage with       │
│    audience, use hashtags, track        │
│    analytics.                           │
└─────────────────────────────────────────┘
```

---

## 🎬 Animation Sequences

### Page Load Animation
```
Timeline:
0ms   → Elements at 0% opacity, slightly scaled down
100ms → Fade in + scale to 100%
200ms → Complete with smooth ease-out
```

### Card Stagger Animation (4-card grid)
```
Card 1: Start 0ms,   Duration 400ms
Card 2: Start 50ms,  Duration 400ms
Card 3: Start 100ms, Duration 400ms
Card 4: Start 150ms, Duration 400ms
```

### Hover Animations
```
Card hover:   Lift 4px up + Shadow expansion + Border glow
Button hover: Scale 102% + Brightness increase
Table hover:  Row lift 2px + Shadow on left + Text bold
Input focus:  Lift 2px + Blue shadow 15px blur
```

---

## 📱 Responsive Layouts

### Mobile (< 640px)
```
Full Width Layout:
┌─────────────────────────┐
│      HEADER             │  (Full width)
│  (Single column)        │
├─────────────────────────┤
│     STAT 1              │  (100% width)
├─────────────────────────┤
│     STAT 2              │  (100% width)
├─────────────────────────┤
│     STAT 3              │  (100% width)
├─────────────────────────┤
│     STAT 4              │  (100% width)
├─────────────────────────┤
│   CONTENT AREA          │  (Full width, scrollable)
└─────────────────────────┘
```

### Tablet (640px - 1024px)
```
Two Column Layout:
┌─────────────────────────────────────┐
│         HEADER (Full Width)         │
├──────────────────┬──────────────────┤
│   STAT 1         │   STAT 2         │
├──────────────────┼──────────────────┤
│   STAT 3         │   STAT 4         │
├─────────────────────────────────────┤
│      CONTENT AREA (Full Width)      │
└─────────────────────────────────────┘
```

### Desktop (> 1024px)
```
Four Column Layout:
┌────────────────────────────────────────────────┐
│              HEADER (Full Width)               │
├─────────┬──────────┬──────────┬───────────────┤
│ STAT 1  │ STAT 2   │ STAT 3   │ STAT 4        │
├────────────────────────────────────────────────┤
│          CONTENT AREA (Full Width)             │
│          (3 Column Grid or Table)              │
└────────────────────────────────────────────────┘
```

---

## 🎨 Color Reference

### Primary Colors
```
Blue:      #3B82F6  (Dashboard, Email)
Green:     #10B981  (Sponsors, Success)
Orange:    #F59E0B  (Notifications, Warnings)
Red:       #EF4444  (Errors, Danger)
Purple:    #8B5CF6  (Social Media, Contact)
Cyan:      #06B6D4  (SMS)
```

### Gray Scale
```
Text Primary:   #111827  (Dark text)
Text Secondary: #6B7280  (Gray text)
Text Light:     #9CA3AF  (Light gray)
Background:     #FFFFFF  (White)
Background Alt: #F9FAFB  (Light gray)
Borders:        #E5E7EB  (Light border)
```

---

## 📏 Spacing System

```
Base Unit: 16px (1rem)

Spacing:
- 2px   = xs (0.125rem)
- 4px   = sm (0.25rem)
- 8px   = md (0.5rem)
- 12px  = lg (0.75rem)
- 16px  = xl (1rem)
- 24px  = 2xl (1.5rem)
- 32px  = 3xl (2rem)
- 48px  = 4xl (3rem)
- 64px  = 5xl (4rem)

Card Padding: 24px (1.5rem)
Header Padding: 32px (2rem)
Grid Gap: 24px (1.5rem)
```

---

## 📊 Typography Scale

```
H1: 2.25rem (36px) | Weight 800 | -0.02em
H2: 1.875rem (30px) | Weight 700 | -0.015em
H3: 1.5rem (24px) | Weight 700
H4: 1.25rem (20px) | Weight 600
H5: 1.125rem (18px) | Weight 600

Body: 1rem (16px) | Weight 400 | Line 1.6
Small: 0.875rem (14px) | Weight 400
Label: 0.875rem (14px) | Weight 600 | Uppercase
```

---

## ✅ Quality Checklist

When implementing a new page, ensure:

- [ ] Header has unique gradient color
- [ ] 4 stat cards visible (or appropriate number)
- [ ] Responsive on mobile (1 col), tablet (2 col), desktop (4 col)
- [ ] Hover effects on cards & buttons
- [ ] Animations smooth & under 600ms
- [ ] Dark mode supported
- [ ] Accessibility: Proper color contrast
- [ ] Forms functional with proper styling
- [ ] Table rows animate in staggered
- [ ] Info/Tips box at bottom
- [ ] Professional typography hierarchy
- [ ] Consistent spacing & padding

---

**Design System Version**: 1.0  
**Last Updated**: Current Session  
**Status**: ✅ Complete & Production Ready
