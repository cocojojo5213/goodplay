import { createI18n } from 'vue-i18n'
import jaMessages from './messages/ja.json'

const messages = {
  ja: jaMessages
}

const i18n = createI18n({
  legacy: false,
  locale: 'ja',
  fallbackLocale: 'ja',
  messages,
  globalInjection: true
})

export default i18n