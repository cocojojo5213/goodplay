import { useAuthStore } from '@/stores/auth'

/**
 * ロールチェック用コンポーザブル
 */

export function useRoleCheck() {
  const authStore = useAuthStore()

  /**
   * 現在のユーザーが指定ロールを持つか確認
   * @param {string|string[]} requiredRoles - 必要なロール
   * @returns {boolean}
   */
  const hasRole = (requiredRoles) => {
    if (!authStore.user) return false
    
    const roles = Array.isArray(requiredRoles) ? requiredRoles : [requiredRoles]
    return roles.includes(authStore.user.role)
  }

  /**
   * 管理者かどうか確認
   * @returns {boolean}
   */
  const isAdmin = () => {
    return hasRole('admin')
  }

  /**
   * マネージャーか管理者かどうか確認
   * @returns {boolean}
   */
  const isManagerOrAdmin = () => {
    return hasRole(['admin', 'manager'])
  }

  /**
   * 複数ロールのいずれかを持つか確認
   * @param {string[]} roles - チェック対象ロール配列
   * @returns {boolean}
   */
  const hasAnyRole = (roles) => {
    if (!authStore.user) return false
    return roles.includes(authStore.user.role)
  }

  /**
   * 全てのロールを持つか確認
   * @param {string[]} roles - チェック対象ロール配列
   * @returns {boolean}
   */
  const hasAllRoles = (roles) => {
    if (!authStore.user) return false
    return roles.every(role => role === authStore.user.role)
  }

  return {
    hasRole,
    isAdmin,
    isManagerOrAdmin,
    hasAnyRole,
    hasAllRoles
  }
}
