import es from '../i18n/es.json';
import en from '../i18n/en.json';

const translations: Record<string, Record<string, unknown>> = { es, en };

function getNested(obj: unknown, path: string): string {
  const keys = path.split('.');
  let current: unknown = obj;
  for (const key of keys) {
    if (current && typeof current === 'object' && key in (current as Record<string, unknown>)) {
      current = (current as Record<string, unknown>)[key];
    } else {
      return path;
    }
  }
  return typeof current === 'string' ? current : path;
}

export function useTranslations(locale: string) {
  const dict = translations[locale] || translations.es;
  return (key: string): string => getNested(dict, key);
}

export function getTranslations(locale: string) {
  return translations[locale] || translations.es;
}
