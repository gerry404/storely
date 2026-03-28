import { computed, ref } from 'vue'
import { useAuth } from './useAuth'

const showUpgradeModal = ref(!localStorage.getItem('storely-upgrade-dismissed'))

export function usePlan() {
  const { shop, planInfo, api } = useAuth()

  const currentPlan = computed(() => planInfo.value?.current_plan || shop.value?.plan || 'free')
  const isFree = computed(() => currentPlan.value === 'free')
  const isStarter = computed(() => currentPlan.value === 'starter')
  const isPro = computed(() => currentPlan.value === 'pro')

  const productLimit = computed(() => {
    const limit = planInfo.value?.product_limit
    return limit === -1 ? Infinity : (limit ?? 5)
  })

  const productCount = computed(() => planInfo.value?.product_count ?? 0)
  const imageLimit = computed(() => planInfo.value?.image_limit ?? 1)
  const commissionRate = computed(() => planInfo.value?.commission_rate ?? 15)

  const canAddProduct = computed(() => productCount.value < productLimit.value)

  const canUseFeature = (feature) => {
    return planInfo.value?.features?.[feature] ?? false
  }

  const requiredPlanForFeature = (feature) => {
    const featureMap = {
      analytics_basic: 'starter',
      analytics_full: 'pro',
      order_management: 'starter',
      stock_management: 'pro',
      promotions: 'pro',
      preorders: 'pro',
      verified_badge: 'pro',
      branding_removed: 'pro',
      branding_reduced: 'starter',
      digital_products: 'free',
      export: 'pro',
      advanced_management: 'pro',
      whatsapp_button: 'starter',
    }
    return featureMap[feature] || 'pro'
  }

  const planLabel = computed(() => {
    const labels = { free: 'Gratuit', starter: 'Starter', pro: 'Pro' }
    return labels[currentPlan.value] || 'Gratuit'
  })

  const dismissUpgradeModal = () => {
    showUpgradeModal.value = false
    localStorage.setItem('storely-upgrade-dismissed', '1')
  }

  const refreshPlanInfo = async () => {
    try {
      const data = await api('/api/subscription')
      if (data.plan_info) {
        const { planInfo: pi } = useAuth()
        pi.value = data.plan_info
        localStorage.setItem('storely-plan-info', JSON.stringify(data.plan_info))
      }
    } catch {
      // silent
    }
  }

  return {
    currentPlan,
    isFree,
    isStarter,
    isPro,
    productLimit,
    productCount,
    imageLimit,
    commissionRate,
    canAddProduct,
    canUseFeature,
    requiredPlanForFeature,
    planLabel,
    showUpgradeModal,
    dismissUpgradeModal,
    refreshPlanInfo,
  }
}
