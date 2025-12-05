# CSS Build Notes

## Overview
This document explains how the CSS is built and why the built assets are committed to the repository.

## Background
The website CSS was broken because the Vite build assets were missing. This happened because:

1. **Tailwind CSS v4 Architecture**: The project uses Tailwind CSS v4, which requires building CSS assets at deployment time
2. **Build Assets Not Committed**: Initially, `/public/build` was in `.gitignore`, so built CSS files weren't in the repository
3. **Deployment Process**: If the deployment process doesn't run `npm run build`, the CSS files won't be available

## Solution
The built CSS and JavaScript assets are now committed to the repository to ensure the website works even if the build step is skipped during deployment.

## Tailwind CSS v4 Configuration
Tailwind CSS v4 uses a different configuration approach than v3:

- **v3**: Custom colors defined in `tailwind.config.js`
- **v4**: Custom colors defined in CSS using the `@theme` directive in `resources/css/app.css`

See `resources/css/app.css` for the `@theme` block containing all custom colors:
- `homelab-*` color palette (50-950)
- `gh-*` GitHub-style dark theme colors

## Build Process

### Development
```bash
npm install
npm run dev
```

### Production
```bash
npm install
npm run build
```

The build process:
1. Reads `resources/css/app.css` with Tailwind directives and `@theme` configuration
2. Processes all Blade templates to find used CSS classes
3. Generates optimized CSS with only the classes actually used
4. Outputs to `public/build/assets/`

## Important Files
- `resources/css/app.css` - Source CSS with Tailwind directives and custom theme
- `public/build/` - Built CSS and JS assets (now committed to git)
- `tailwind.config.js` - Legacy config (kept for reference, not used in v4)
- `postcss.config.js` - PostCSS configuration for Tailwind v4
- `vite.config.js` - Vite build configuration

## Best Practices

### For Deployment
1. **Preferred**: Run `npm run build` during deployment to generate fresh assets
2. **Fallback**: Use committed assets if build step is not available

### For Development
1. Always run `npm run dev` or `npm run build` after pulling changes
2. If CSS classes don't work, rebuild assets with `npm run build`
3. Keep built assets committed so production deployments have a fallback

### For Adding New Colors
1. Edit the `@theme` block in `resources/css/app.css`
2. Add new CSS variables like `--color-my-color: #hexcode;`
3. Run `npm run build` to regenerate CSS
4. Commit the updated `resources/css/app.css` and `public/build/` files

## Troubleshooting

### CSS Not Loading
1. Check if `public/build/manifest.json` exists
2. Verify files in `public/build/assets/` exist
3. Run `npm run build` to regenerate assets
4. Check browser console for 404 errors

### Custom Colors Not Working
1. Verify colors are defined in `@theme` block in `resources/css/app.css`
2. Check color names use format `--color-prefix-variant`
3. Rebuild CSS with `npm run build`
4. Clear browser cache

### After Pulling Changes
```bash
npm install  # Update dependencies if needed
npm run build  # Rebuild assets
```

## Related Documentation
- [Tailwind CSS v4 Documentation](https://tailwindcss.com/docs)
- [Vite Documentation](https://vitejs.dev/)
- [Laravel Vite Integration](https://laravel.com/docs/vite)
