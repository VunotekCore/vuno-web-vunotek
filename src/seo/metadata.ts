import es from '../i18n/es.json'
import en from '../i18n/en.json'

const i18n = { es, en } as const

export const SEO = {
  global: {
    siteName: 'Vunotek',
    siteUrl: 'https://vunotek.com',
    currentYear: 2026,
    ogDefaultImage: {
      src: 'https://vunotek.com/logo.webp',
      width: 1200,
      height: 630,
      format: 'webp' as const,
    },
  },

  organizationSchema: {
    '@context': 'https://schema.org',
    '@type': 'Organization',
    name: 'Vunotek',
    url: 'https://vunotek.com',
    logo: 'https://vunotek.com/logo.webp',
    foundingDate: '2024',
    contactPoint: {
      '@type': 'ContactPoint',
      email: 'comercial@vunotek.com',
      contactType: 'sales',
    },
    sameAs: ['https://github.com/vunotek', 'https://linkedin.com/company/vunotek'],
    description: {
      es: 'Ingeniería de software de precisión para negocios modernos. Arquitectura backend robusta con .NET y Node.js, automatización de procesos empresariales y desarrollo de software a la medida con Vue.js, Astro y Salesforce.',
      en: 'Engineering Precision for Modern Business. Robust backend architecture with .NET and Node.js, enterprise process automation, and custom software development with Vue.js, Astro, and Salesforce.',
    },
  },

  breadcrumbs: {
    es: [
      { name: 'Inicio', path: '/' },
      { name: 'Servicios', path: '/servicios/' },
      { name: 'Proyectos', path: '/proyectos/' },
      { name: 'Blog', path: '/blog/' },
      { name: 'Nosotros', path: '/about/' },
      { name: 'Contacto', path: '/contacto/' },
      { name: 'Privacidad', path: '/privacidad/' },
      { name: 'Términos', path: '/terminos/' },
    ],
    en: [
      { name: 'Home', path: '/en/' },
      { name: 'Services', path: '/en/servicios/' },
      { name: 'Projects', path: '/en/proyectos/' },
      { name: 'Blog', path: '/en/blog/' },
      { name: 'About', path: '/en/about/' },
      { name: 'Contact', path: '/en/contacto/' },
      { name: 'Privacy', path: '/en/privacy/' },
      { name: 'Terms', path: '/en/terms/' },
    ],
  },

  pages: {
    home: (locale: 'es' | 'en') => ({
      title:
        locale === 'es'
          ? 'Vunotek | Ingeniería de Software, Desarrollo a Medida y Automatización de Procesos'
          : 'Vunotek | Software Engineering, Custom Development & Enterprise Automation',
      description: (i18n[locale] as any).home.hero.description,
    }),

    notFound: (locale: 'es' | 'en') => ({
      robots: 'noindex, follow' as const,
      title:
        locale === 'es' ? 'Página no encontrada | Vunotek' : 'Page Not Found | Vunotek',
      description:
        locale === 'es'
          ? 'La página que buscas no existe o fue movida. Explora nuestros servicios de ingeniería de software, automatización de procesos y desarrollo a la medida.'
          : 'The page you are looking for does not exist or has been moved. Explore our software engineering, enterprise automation, and custom development services.',
      canonical: locale === 'es' ? '/' : '/en/',
    }),

    services: (locale: 'es' | 'en') => ({
      title: (i18n[locale] as any).services_page.meta.title,
      description:
        locale === 'es'
          ? 'Ingeniería de software con .NET, Node.js, SQL Server y Salesforce. APIs robustas, automatización y arquitectura de datos empresariales.'
          : 'Software engineering with .NET, Node.js, SQL Server and Salesforce. Robust APIs, automation, and enterprise data architecture.',
    }),

    projects: (locale: 'es' | 'en') => ({
      title: (i18n[locale] as any).projects_page.meta.title,
      description:
        locale === 'es'
          ? 'Portafolio de Vunotek: VUNO POS, Shop, Reservations, Envíos, CRM, Drive. Soluciones empresariales de ingeniería de software.'
          : 'Vunotek portfolio: VUNO POS, Shop, Reservations, Shipping, CRM, Drive. Enterprise software engineering solutions.',
    }),

    contact: (locale: 'es' | 'en') => ({
      title: (i18n[locale] as any).contact_page.meta.title,
      description: (i18n[locale] as any).contact_page.hero.description,
    }),

    about: (locale: 'es' | 'en') => ({
      title: (i18n[locale] as any).about.meta.title,
      description: (i18n[locale] as any).about.hero.description,
    }),

    blog: (locale: 'es' | 'en') => ({
      title: (i18n[locale] as any).blog.hero.title + ' | Vunotek',
      description: (i18n[locale] as any).blog.hero.desc,
    }),

    privacy: (locale: 'es' | 'en') => ({
      title: (i18n[locale] as any).privacy_page.meta.title,
      description:
        locale === 'es'
          ? 'Política de privacidad de Vunotek. Conoce cómo recopilamos, protegemos y utilizamos tus datos personales.'
          : 'Vunotek privacy policy. Learn how we collect, protect, and use your personal data.',
    }),

    terms: (locale: 'es' | 'en') => ({
      title: (i18n[locale] as any).terms_page.meta.title,
      description:
        locale === 'es'
          ? 'Términos de servicio de Vunotek. Condiciones de uso del sitio web y servicios de ingeniería de software.'
          : 'Vunotek terms of service. Website usage conditions and software engineering services.',
    }),
  },

  images: {
    portfolio: {
      pos: {
        alt: {
          es: 'VUNO POS - Sistema de Punto de Venta Empresarial con interfaz moderna y offline-first',
          en: 'VUNO POS - Enterprise Point of Sale with modern interface and offline-first architecture',
        },
        features: {
          'pos-interface': {
            alt: { es: 'Interfaz POS con búsqueda de productos y carrito de compras', en: 'POS interface with product search and shopping cart' },
          },
          'pos-payments': {
            alt: { es: 'Múltiples métodos de pago en POS: efectivo, tarjeta y cheque', en: 'Multiple POS payment methods: cash, card and check' },
          },
          'pos-cashdrawer': {
            alt: { es: 'Administración de caja POS: apertura, cierre y control de efectivo', en: 'POS cash drawer: open/close management and cash control' },
          },
          'pos-offline': {
            alt: { es: 'Ventas offline POS con cola de sincronización automática', en: 'POS offline sales with automatic sync queue' },
          },
          'pos-inventory': {
            alt: { es: 'Inventario en tiempo real con variantes, lotes y alertas de stock', en: 'Real-time inventory with variants, lots and low-stock alerts' },
          },
          'pos-transfers': {
            alt: { es: 'Transferencias de inventario entre ubicaciones', en: 'Stock transfers between locations' },
          },
          'pos-suppliers': {
            alt: { es: 'Gestión de proveedores y órdenes de compra en POS', en: 'Supplier management and purchase orders in POS' },
          },
          'pos-desktop': {
            alt: { es: 'App de escritorio nativa Electron de VUNO POS', en: 'VUNO POS native desktop Electron app' },
          },
          'pos-multitenant': {
            alt: { es: 'Arquitectura multi-tenant de VUNO POS con control de accesos', en: 'VUNO POS multi-tenant architecture with access control' },
          },
          'pos-dashboards': {
            alt: { es: 'Dashboards de administración y reportes en tiempo real', en: 'Admin dashboards and real-time reports' },
          },
        },
      },
      shop: {
        alt: {
          es: 'VUNO SHOP - Plataforma de E-Commerce de alto rendimiento con Astro y Vue.js',
          en: 'VUNO SHOP - High-performance E-Commerce platform with Astro and Vue.js',
        },
        features: {
          'shop-frontend': {
            alt: { es: 'Frontend Astro de tienda online con Core Web Vitals optimizados', en: 'Astro-powered storefront with optimized Core Web Vitals' },
          },
          'shop-catalog': {
            alt: { es: 'Catálogo de productos con filtros avanzados en E-Commerce', en: 'Product catalog with advanced filtering in E-Commerce' },
          },
          'shop-gallery': {
            alt: { es: 'Galería interactiva de imágenes con lupa y modal', en: 'Interactive image gallery with magnifier and modal' },
          },
          'shop-cart': {
            alt: { es: 'Carrito de compras con persistencia y sincronización', en: 'Shopping cart with persistence and server sync' },
          },
          'shop-payments': {
            alt: { es: 'Pagos con tarjeta Stripe y transferencia bancaria', en: 'Stripe card payments and bank transfer' },
          },
          'shop-currency': {
            alt: { es: 'Soporte multi-moneda con tasas de cambio configurables', en: 'Multi-currency support with configurable exchange rates' },
          },
          'shop-accounts': {
            alt: { es: 'Cuentas de cliente con historial de pedidos', en: 'Customer accounts with order history' },
          },
          'shop-admin': {
            alt: { es: 'Panel administrativo completo de E-Commerce', en: 'Comprehensive E-Commerce admin panel' },
          },
          'shop-inventory': {
            alt: { es: 'Gestión de inventario con matriz de variantes', en: 'Inventory management with variant matrix' },
          },
          'shop-security': {
            alt: { es: 'Autenticación 2FA TOTP para seguridad del panel', en: 'TOTP 2FA authentication for panel security' },
          },
          'shop-notifications': {
            alt: { es: 'Notificaciones email automatizadas con plantillas HTML', en: 'Automated email notifications with HTML templates' },
          },
          'shop-i18n': {
            alt: { es: 'Sistema de internacionalización ES/EN en E-Commerce', en: 'ES/EN internationalization system in E-Commerce' },
          },
        },
      },
      hotel: {
        alt: {
          es: 'VUNO RESERVATIONS - Sistema de Gestión Hotelera PMS con panel de recepción',
          en: 'VUNO RESERVATIONS - Hotel PMS with front desk reception panel',
        },
        features: {
          'hotel-website': {
            alt: { es: 'Sitio web de reservas de hotel con disponibilidad en tiempo real', en: 'Hotel booking website with real-time availability' },
          },
          'hotel-rooms': {
            alt: { es: 'Páginas de detalle de habitaciones con galería de imágenes', en: 'Room detail pages with image gallery' },
          },
          'hotel-bookings': {
            alt: { es: 'Reservas online con pagos Stripe en sistema hotelero', en: 'Online bookings with Stripe payments in hotel system' },
          },
          'hotel-reception': {
            alt: { es: 'Panel de recepción hotelera con cuadrícula de habitaciones', en: 'Hotel reception panel with room grid view' },
          },
          'hotel-checkin': {
            alt: { es: 'Flujo de check-in/check-out con facturación automática', en: 'Check-in/check-out workflow with auto invoicing' },
          },
          'hotel-housekeeping': {
            alt: { es: 'Gestión de housekeeping: limpieza y mantenimiento de habitaciones', en: 'Housekeeping management: cleaning and maintenance' },
          },
          'hotel-calendar': {
            alt: { es: 'Calendario de ocupación hotelera con vista Gantt', en: 'Hotel occupancy calendar with Gantt view' },
          },
          'hotel-guests': {
            alt: { es: 'Directorio de huéspedes con historial de estancias', en: 'Guest directory with stay history' },
          },
          'hotel-lifecycle': {
            alt: { es: 'Ciclo de vida de reservas: confirmada, check-in, check-out', en: 'Reservation lifecycle: confirmed, check-in, check-out' },
          },
          'hotel-admin': {
            alt: { es: 'Panel administrativo de PMS hotelero completo', en: 'Comprehensive hotel PMS admin panel' },
          },
          'hotel-security': {
            alt: { es: 'Autenticación 2FA con control de acceso por roles en hotel', en: 'TOTP 2FA with role-based access in hotel system' },
          },
          'hotel-notifications': {
            alt: { es: 'Notificaciones email automatizadas para reservas hoteleras', en: 'Automated email notifications for hotel bookings' },
          },
        },
      },
      logistics: {
        alt: {
          es: 'VUNO ENVÍOS - Plataforma Logística Miami-Nicaragua con rastreo en tiempo real',
          en: 'VUNO ENVÍOS - Miami-Nicaragua Logistics Platform with real-time tracking',
        },
        features: {
          'logistics-locker': {
            alt: { es: 'Casillero virtual en Miami para paquetes internacionales', en: 'Virtual Miami locker for international packages' },
          },
          'logistics-shipping': {
            alt: { es: 'Envíos aéreos y marítimos con cálculo de tarifas', en: 'Air and sea freight shipping with rate calculation' },
          },
          'logistics-tracking': {
            alt: { es: 'Rastreo de paquetes multi-paso con stepper animado', en: 'Multi-step package tracking with animated stepper' },
          },
          'logistics-calculator': {
            alt: { es: 'Calculadora de costos de envío por peso y categoría', en: 'Shipping cost calculator by weight and category' },
          },
          'logistics-plans': {
            alt: { es: 'Comparativa de planes de envío: recogida, puerta a puerta', en: 'Shipping plan comparison: pickup, door-to-door' },
          },
          'logistics-locations': {
            alt: { es: 'Ubicaciones de bodega Miami y agencia Nicaragua', en: 'Miami warehouse and Nicaragua agency locations' },
          },
          'logistics-whatsapp': {
            alt: { es: 'Botón flotante de WhatsApp para comunicación directa', en: 'WhatsApp floating button for direct communication' },
          },
          'logistics-contact': {
            alt: { es: 'Formulario de contacto interactivo de logística', en: 'Interactive logistics contact form' },
          },
          'logistics-responsive': {
            alt: { es: 'Diseño responsivo bilingüe para plataforma logística', en: 'Bilingual responsive design for logistics platform' },
          },
          'logistics-guides': {
            alt: { es: 'Guías completas de servicios y procesos logísticos', en: 'Comprehensive logistics service guides' },
          },
          'logistics-insurance': {
            alt: { es: 'Información de seguro de carga para envíos de alto valor', en: 'Cargo insurance for high-value shipments' },
          },
          'logistics-mobile': {
            alt: { es: 'Diseño mobile-first para rastreo en movimiento', en: 'Mobile-first design for on-the-go tracking' },
          },
        },
      },
      crm: {
        alt: {
          es: 'VUNO CRM - Plataforma de Social Selling con bandeja unificada',
          en: 'VUNO CRM - Social Selling Platform with unified inbox',
        },
        features: {
          'crm-inbox': {
            alt: { es: 'Bandeja de entrada social unificada: WhatsApp, Instagram, Facebook', en: 'Unified social inbox: WhatsApp, Instagram, Facebook' },
          },
          'crm-profiles': {
            alt: { es: 'Perfiles de cliente 360° con historial de interacciones', en: '360° customer profiles with interaction history' },
          },
          'crm-kanban': {
            alt: { es: 'Kanban de oportunidades drag-and-drop en CRM', en: 'Opportunity kanban drag-and-drop in CRM' },
          },
          'crm-quotes': {
            alt: { es: 'Motor de cotizaciones con análisis de costos y márgenes', en: 'Quoting engine with cost and margin analysis' },
          },
          'crm-tasks': {
            alt: { es: 'Gestión de actividades y tareas con recordatorios', en: 'Activity and task management with reminders' },
          },
          'crm-notifications': {
            alt: { es: 'Notificaciones push en tiempo real vía WebSocket', en: 'Real-time push notifications via WebSocket' },
          },
          'crm-rbac': {
            alt: { es: 'Control de acceso basado en roles con permisos granulares', en: 'Role-based access control with granular permissions' },
          },
          'crm-attendance': {
            alt: { es: 'Control de asistencia con GPS y foto de entrada/salida', en: 'Attendance tracking with GPS and photo check-in/out' },
          },
          'crm-employees': {
            alt: { es: 'Gestión de empleados con departamentos y organigrama', en: 'Employee management with departments and org chart' },
          },
          'crm-dashboards': {
            alt: { es: 'Dashboards interactivos: embudo de ventas e ingresos', en: 'Interactive dashboards: sales funnel and revenue' },
          },
          'crm-saas': {
            alt: { es: 'Arquitectura SaaS multitenencia con datos aislados', en: 'Multi-tenant SaaS architecture with isolated data' },
          },
          'crm-pwa': {
            alt: { es: 'Soporte PWA con modo offline y modo oscuro en CRM', en: 'PWA support with offline caching and dark mode in CRM' },
          },
        },
      },
    },
    blogCard: {
      alt: {
        es: 'Artículo del blog técnico de Vunotek sobre ingeniería de software',
        en: 'Vunotek technical blog article on software engineering',
      },
    },
  },

  keywords: {
    es: [
      'Vunotek',
      'Ingeniería de Software',
      'Desarrollo a la Medida',
      'Software Robusto',
      'Automatización de Procesos',
      'Sistemas de Gestión',
      'vuno-shop',
      'vuno-rentacar',
      'vuno-hotel',
      'Desarrollo Backend',
      'Arquitectura Salesforce',
      '.NET Core',
      'Node.js',
      'Vue.js',
      'Astro',
    ],
    en: [
      'Vunotek',
      'Software Engineering',
      'Custom Software Development',
      'Engineering Precision for Modern Business',
      'Enterprise Automation',
      'High-Performance Solutions',
      'Backend Architecture',
      'Salesforce Consulting',
      '.NET Core',
      'Node.js',
      'Vue.js',
      'Astro',
      'Cloud Infrastructure',
    ],
  },
}
