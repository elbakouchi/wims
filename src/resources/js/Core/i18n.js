import { createI18n } from 'vue-i18n';

import en from '@r/lang/en.json';
import es from '@r/lang/es.json';
import pt from '@r/lang/pt.json';

const messages = { en, es, pt };

const i18n = createI18n({
  messages,
  missingWarn: false,
  fallbackWarn: false,
  fallbackLocale: 'en',
  warnHtmlMessage: false,
  locale: window.Locale || 'en',
});

export default i18n;
