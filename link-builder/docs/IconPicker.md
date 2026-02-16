# IconPicker Component - User Guide

## 🎨 Usage

The `IconPicker` component allows visual selection of icons from two categories:

### Social Media Icons
- Facebook
- Instagram
- TikTok
- YouTube
- Twitter/X
- LinkedIn
- Twitch
- Spotify
- GitHub
- WhatsApp
- Email
- Phone

### UI Icons (Lucide)
- link, star, heart, shop, play
- image, music, mail, phone, map
- calendar, file, download, external
- gift, search, home, user, settings

## 📝 Integration in Schemas

To use the IconPicker in a block schema:

```php
[
    'name' => 'icon',
    'label' => 'Icon',
    'type' => 'icon',  // ← This triggers IconPicker
    'placeholder' => 'Choose an icon',
    'rules' => ['nullable', 'string', 'max:50'],
]
```

## 🖱️ User Experience

1. Click on the icon field
2. Choose between "Social Media" or "UI Icons" tabs
3. Click an icon to select it
4. Selected icon shows with eSponsor brand color (#22B8A6)
5. Icon name is saved as string (e.g., "facebook", "star")

## 🎯 Example: LinksBlock

```json
{
  "type": "links",
  "props": {
    "links": [
      {
        "label": "Visit Website",
        "url": "https://example.com",
        "icon": "link",        ← Selected from IconPicker
        "style": "primary"
      },
      {
        "label": "Follow on Instagram",
        "url": "https://instagram.com/user",
        "icon": "instagram",   ← Selected from IconPicker
        "style": "secondary"
      }
    ]
  }
}
```

## 🔧 Technical Details

- **Component:** `resources/js/Components/IconPicker.vue`
- **Props:** `modelValue` (string)
- **Emits:** `update:modelValue`
- **Social Icons:** SVG paths from simple-icons
- **UI Icons:** Lucide Vue components
- **Styling:** eSponsor brand colors with hover effects

## 🎨 Visual Design

- Grid layout with 80px cells
- Icon size: 24px
- Selected state: eSponsor green (#22B8A6)
- Hover effects: lift and color change
- Max height: 300px with scroll
