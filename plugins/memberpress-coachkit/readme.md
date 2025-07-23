# memberpress-coachkit
MemberPress CoachKit Add-on

### Assets
All assets are in public folder -- CSS, JS, Images. It's important to install all dependencies to edit CSS and JS files

```
cd public
npm i
```

#### CSS

[![Tailwind CSS](https://img.shields.io/badge/Tailwind%20CSS-^2.0.0-38B2AC?style=flat&logo=tailwind-css&logoColor=white)](https://tailwindcss.com)
[![SCSS](https://img.shields.io/badge/SCSS-Styles-bf4080?style=flat&logo=sass&logoColor=white)](https://sass-lang.com/)

All styles are compiled from SCSS in `public/css/sass` folder. 
All admin styles are to be found in `public/css/sass/admin.scss` while frontend styles are contained in `public/css/sass/tailwind.scss` or `public/css/sass/tailwind-rl.scss`. The latter is loaded when ReadyLaunch (RL) is active while the former is loaded when RL is not active.

To compile the CSS please run this `watch` command to immediately reflect all your changes or 

```
npx mix watch
```

Or run the build command when you are done

```
npx mix --production
```

The bundler, Laravel Mix, is a great wrapper for Webpack and the configurations can be found in `public/webpack.mix.js`

#### JS
Admin scripts are jQuery files and do not require any asset compilation. However, the 

### Tests
This plugin has three layers of tests
- Unit tests using BrainMonkey and Mockery
- Integration tests using the default WPUnitTest 
- End to End (E2E) tests using WPBrowser and Codeception

#### Unit Tests

