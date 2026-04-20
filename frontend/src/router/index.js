import { createRouter, createWebHistory } from 'vue-router'

const routes = [
  {
    path: '/',
    name: 'home',
    component: () => import('../views/HomePage.vue')
  },
  {
    path: '/pricing',
    name: 'pricing',
    component: () => import('../views/PricingPage.vue')
  },
  {
    path: '/examples',
    name: 'examples',
    component: () => import('../views/ExamplesPage.vue')
  },
  {
    path: '/contact',
    name: 'contact',
    component: () => import('../views/ContactPage.vue')
  },
  {
    path: '/help',
    name: 'help',
    component: () => import('../views/HelpPage.vue')
  },
  {
    path: '/terms',
    name: 'terms',
    component: () => import('../views/TermsPage.vue')
  },
  {
    path: '/privacy',
    name: 'privacy',
    component: () => import('../views/PrivacyPage.vue')
  },
  {
    path: '/marketplace',
    name: 'marketplace',
    component: () => import('../views/MarketplacePage.vue')
  },
  {
    path: '/login',
    name: 'login',
    component: () => import('../views/AuthPage.vue'),
    meta: { authMode: 'login' }
  },
  {
    path: '/register',
    name: 'register',
    component: () => import('../views/AuthPage.vue'),
    meta: { authMode: 'register' }
  },
  {
    path: '/auth/google/callback',
    name: 'google-callback',
    component: () => import('../views/GoogleCallbackPage.vue'),
  },
  {
    path: '/dashboard',
    name: 'dashboard',
    component: () => import('../views/dashboard/DashboardLayout.vue'),
    meta: { requiresAuth: true },
    children: [
      { path: '', name: 'dashboard-home', component: () => import('../views/dashboard/DashboardHome.vue') },
      { path: 'products', name: 'dashboard-products', component: () => import('../views/dashboard/DashboardProducts.vue') },
      { path: 'products/new', name: 'dashboard-product-new', component: () => import('../views/dashboard/ProductFormPage.vue') },
      { path: 'products/:id/edit', name: 'dashboard-product-edit', component: () => import('../views/dashboard/ProductFormPage.vue') },
      { path: 'orders', name: 'dashboard-orders', component: () => import('../views/dashboard/DashboardOrders.vue') },
      { path: 'promotions', name: 'dashboard-promotions', component: () => import('../views/dashboard/DashboardPromotions.vue') },
      { path: 'delivery', name: 'dashboard-delivery', component: () => import('../views/dashboard/DashboardDelivery.vue') },
      { path: 'customize', name: 'dashboard-customize', component: () => import('../views/dashboard/DashboardCustomize.vue') },
      { path: 'settings', name: 'dashboard-settings', component: () => import('../views/dashboard/DashboardSettings.vue') },
      { path: 'upgrade', name: 'dashboard-upgrade', component: () => import('../views/dashboard/DashboardUpgrade.vue') },
      { path: 'chat', name: 'dashboard-chat', component: () => import('../views/dashboard/DashboardChat.vue') },
      { path: 'referrals', name: 'dashboard-referrals', component: () => import('../views/dashboard/DashboardReferrals.vue') },
    ]
  },
  {
    path: '/payment/callback',
    name: 'payment-callback',
    component: () => import('../views/PaymentCallbackPage.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/payment/order-callback',
    name: 'order-payment-callback',
    component: () => import('../views/OrderPaymentCallbackPage.vue'),
  },
  {
    path: '/admin',
    component: () => import('../views/admin/AdminLayout.vue'),
    meta: { requiresAuth: true, requiresAdmin: true },
    children: [
      { path: '', name: 'admin-dashboard', component: () => import('../views/admin/AdminDashboard.vue') },
      { path: 'users', name: 'admin-users', component: () => import('../views/admin/AdminUsers.vue') },
      { path: 'shops', name: 'admin-shops', component: () => import('../views/admin/AdminShops.vue') },
      { path: 'orders', name: 'admin-orders', component: () => import('../views/admin/AdminOrders.vue') },
      { path: 'payments', name: 'admin-payments', component: () => import('../views/admin/AdminPayments.vue') },
      { path: 'subscriptions', name: 'admin-subscriptions', component: () => import('../views/admin/AdminSubscriptions.vue') },
      { path: 'badges', name: 'admin-badges', component: () => import('../views/admin/AdminBadges.vue') },
      { path: 'referrals', name: 'admin-referrals', component: () => import('../views/admin/AdminReferrals.vue') },
      { path: 'promotions', name: 'admin-promotions', component: () => import('../views/admin/AdminPromotions.vue') },
      { path: 'settings', name: 'admin-settings', component: () => import('../views/admin/AdminSettings.vue') },
      { path: 'logs', name: 'admin-logs', component: () => import('../views/admin/AdminLogs.vue') },
    ]
  },
  {
    path: '/:shopSlug/:productSlug',
    name: 'product-page',
    component: () => import('../views/storefront/ProductPage.vue')
  },
  {
    path: '/:slug',
    name: 'storefront',
    component: () => import('../views/storefront/StorefrontPage.vue')
  },
  {
    path: '/:pathMatch(.*)*',
    name: 'not-found',
    component: () => import('../views/NotFoundPage.vue')
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior() {
    return { top: 0 }
  }
})

router.beforeEach((to) => {
  if (to.matched.some(r => r.meta.requiresAuth)) {
    const token = localStorage.getItem('storely-token')
    if (!token) return { path: '/login' }
  }
  if (to.matched.some(r => r.meta.requiresAdmin)) {
    const user = JSON.parse(localStorage.getItem('storely-user') || 'null')
    if (!user || !['admin', 'superadmin'].includes(user.role)) {
      return { path: '/dashboard' }
    }
  }
})

export default router
