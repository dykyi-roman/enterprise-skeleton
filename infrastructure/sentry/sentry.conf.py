from sentry.conf.server import *

DATABASES = {
    "default": {
        "ENGINE": "sentry.db.postgres",
        "NAME": os.environ.get("SENTRY_DB_NAME"),
        "USER": os.environ.get("SENTRY_DB_USER"),
        "PASSWORD": os.environ.get("SENTRY_DB_PASSWORD"),
        "HOST": os.environ.get("SENTRY_POSTGRES_HOST"),
        "PORT": os.environ.get("SENTRY_POSTGRES_PORT"),
    }
}

# Redis configuration
SENTRY_REDIS_OPTIONS = {
    'hosts': {
        0: {
            'host': os.environ.get('SENTRY_REDIS_HOST', 'redis-sentry'),
            'port': int(os.environ.get('SENTRY_REDIS_PORT', '6379')),
        }
    }
}

BROKER_URL = 'redis://%s:%s/0' % (
    os.environ.get('SENTRY_REDIS_HOST', 'redis-sentry'),
    os.environ.get('SENTRY_REDIS_PORT', '6379')
)

SENTRY_CACHE = 'sentry.cache.redis.RedisCache'

SENTRY_OPTIONS.update({
    "system.secret-key": os.environ.get("SENTRY_SECRET_KEY"),
    "system.url-prefix": "http://127.0.0.1:9001",  # Updated to match external access URL
    "mail.backend": "smtp",
    "mail.host": os.environ.get("SENTRY_MAIL_HOST"),
    "mail.port": 25,
    "mail.use-tls": False,
})

# Web server configuration
SENTRY_WEB_HOST = '0.0.0.0'
SENTRY_WEB_PORT = 9000
SENTRY_WEB_OPTIONS = {
    'http-socket': '0.0.0.0:9000',
    'workers': 3,
    'threads': 4,
}

# Ensure configuration is loaded from correct path
CONF_ROOT = os.path.dirname(__file__)

# Security settings
CSRF_COOKIE_SECURE = False
CSRF_COOKIE_HTTPONLY = False
CSRF_COOKIE_SAMESITE = None
SESSION_COOKIE_SECURE = False

# Debug settings
DEBUG = True
TEMPLATE_DEBUG = True

# Allow all hosts
ALLOWED_HOSTS = ['*']

# Middleware configuration
MIDDLEWARE_CLASSES = (
    'django.middleware.common.CommonMiddleware',
    'django.contrib.sessions.middleware.SessionMiddleware',
    'django.contrib.auth.middleware.AuthenticationMiddleware',
    'django.contrib.messages.middleware.MessageMiddleware',
    'sentry.middleware.env.SentryEnvMiddleware',
)

# Set proper CSRF failure view
CSRF_FAILURE_VIEW = 'django.views.csrf.csrf_failure'

# Remove CSRF middleware completely
MIDDLEWARE_CLASSES = tuple(
    mw for mw in MIDDLEWARE_CLASSES
    if not mw.endswith('CsrfViewMiddleware')
)

# Allow all origins for Sentry SDK
CORS_ORIGIN_ALLOW_ALL = True
CORS_ALLOW_METHODS = ['POST', 'GET', 'OPTIONS']
CORS_ALLOW_HEADERS = [
    'x-sentry-auth',
    'content-type',
    'origin',
    'accept',
    'authorization',
]

# CSRF Configuration for trusted origins
CSRF_TRUSTED_ORIGINS = [
    # Internal container access (DNS names)
    "http://es-sentry-web:9000",
    "http://es-php:1000",
    "https://es-php:1001",
    
    # Internal container access (IPs)
    "http://172.20.0.8:1000",    # PHP container
    "https://172.20.0.8:1001",   # PHP container (HTTPS)
    "http://172.20.0.9:9000",    # Sentry container
    
    # External access
    "http://localhost:9001",
    "http://127.0.0.1:9001",
    "https://localhost:1001",
    "https://127.0.0.1:1001",
    
    # Additional combinations
    "http://localhost:1000",
    "http://127.0.0.1:1000"
]