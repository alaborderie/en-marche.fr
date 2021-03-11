{
  module.exports = {
    "settings": {
      "import/resolver": {
        "webpack": {
          "config": "webpack.development.js"
        }
      }
    },
    "extends": [
      "eslint:recommended",
      "airbnb-base"
    ],
    "plugins": [
      "react"
    ],
    "parserOptions": {
      "ecmaVersion": 6,
      "sourceType": "module",
      "ecmaFeatures": {
        "jsx": true
      }
    },
    "env": {
      "es6": true,
      "browser": true,
      "node": true,
      "mocha": true,
      "jquery": true
    },
    "globals": {
      "dom": true,
      "find": true,
      "findAll": true,
      "on": true,
      "once": true,
      "off": true,
      "insertAfter": true,
      "remove": true,
      "show": true,
      "hide": true,
      "addClass": true,
      "hasClass": true,
      "toggleClass": true,
      "removeClass": true,
      "App": true,
      "google": true,
      "getUrlParameter": true,
    },
    "rules": {
      "indent": ["error", 4],
      "quotes": ["error", "single"],
      "no-var": ["error"],
      "no-underscore-dangle": "off",
      "semi": ["error", "always"],
      "no-trailing-spaces": ["error"],
      "eol-last": ["error"],
      "yoda": ["error", "always"],
      "max-len": ["error", 120],
      "no-param-reassign": ["off"],
      "no-unused-vars": ["off"],
      "class-methods-use-this": ["off"]
    }
  }
}
