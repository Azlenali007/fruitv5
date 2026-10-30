<?php
/**
 * Cart Service
 * N.A Fresh Fruits & Coconuts
 * Manages cart state in session while strictly validating real-time prices & stock from MySQL.
 */

class CartService {
    private const SESSION_KEY = 'na_cart';

    public static function getCart(): array {
        if (!isset($_SESSION[self::SESSION_KEY]) || !is_array($_SESSION[self::SESSION_KEY])) {
            $_SESSION[self::SESSION_KEY] = [];
        }

        $rawItems = $_SESSION[self::SESSION_KEY];
        $verifiedItems = [];
        $subtotal = 0.00;
        $totalItemsCount = 0;

        foreach ($rawItems as $productId => $qty) {
            $product = Product::getById((int)$productId);
            if (!$product || !$product['is_available'] || $product['stock_quantity'] <= 0) {
                // Remove item if no longer available
                unset($_SESSION[self::SESSION_KEY][$productId]);
                continue;
            }

            // Cap quantity to current available stock
            $safeQty = min((int)$qty, (int)$product['stock_quantity']);
            if ($safeQty <= 0) {
                unset($_SESSION[self::SESSION_KEY][$productId]);
                continue;
            }

            $unitPrice = (float)$product['price'];
            $lineTotal = $unitPrice * $safeQty;
            $subtotal += $lineTotal;
            $totalItemsCount += $safeQty;

            $verifiedItems[] = [
                'product_id' => $product['id'],
                'name' => $product['name'],
                'tagline' => $product['tagline'],
                'price' => $unitPrice,
                'image_url' => $product['image_url'],
                'quantity' => $safeQty,
                'max_stock' => (int)$product['stock_quantity'],
                'subtotal' => $lineTotal
            ];
        }

        return [
            'items' => $verifiedItems,
            'count' => $totalItemsCount,
            'subtotal' => $subtotal
        ];
    }

    public static function addItem(int $productId, int $quantity = 1): array {
        $product = Product::getById($productId);
        if (!$product) {
            return ['success' => false, 'error' => 'Product not found.'];
        }
        if (!$product['is_available'] || $product['stock_quantity'] <= 0) {
            return ['success' => false, 'error' => 'This product is currently out of stock.'];
        }

        if (!isset($_SESSION[self::SESSION_KEY])) {
            $_SESSION[self::SESSION_KEY] = [];
        }

        $currentQty = $_SESSION[self::SESSION_KEY][$productId] ?? 0;
        $newQty = $currentQty + $quantity;

        if ($newQty > $product['stock_quantity']) {
            $newQty = $product['stock_quantity'];
        }

        $_SESSION[self::SESSION_KEY][$productId] = max(1, $newQty);

        return ['success' => true, 'cart' => self::getCart()];
    }

    public static function updateQuantity(int $productId, int $quantity): array {
        if (!isset($_SESSION[self::SESSION_KEY])) {
            $_SESSION[self::SESSION_KEY] = [];
        }

        if ($quantity <= 0) {
            return self::removeItem($productId);
        }

        $product = Product::getById($productId);
        if (!$product) {
            return ['success' => false, 'error' => 'Product not found.'];
        }

        $safeQty = min($quantity, (int)$product['stock_quantity']);
        $_SESSION[self::SESSION_KEY][$productId] = $safeQty;

        return ['success' => true, 'cart' => self::getCart()];
    }

    public static function removeItem(int $productId): array {
        if (isset($_SESSION[self::SESSION_KEY][$productId])) {
            unset($_SESSION[self::SESSION_KEY][$productId]);
        }
        return ['success' => true, 'cart' => self::getCart()];
    }

    public const COUPON_SESSION_KEY = 'na_applied_coupon';

    public static function clear(): void {
        $_SESSION[self::SESSION_KEY] = [];
        unset($_SESSION[self::COUPON_SESSION_KEY]);
    }

    /**
     * Apply coupon to current cart session after verifying all business rules.
     */
    public static function applyCoupon(string $code, ?int $customerId = null, string $phone = '', string $fulfilment = 'delivery'): array {
        $cart = self::getCart();
        $subtotal = (float)$cart['subtotal'];

        if ($subtotal <= 0) {
            return ['success' => false, 'error' => 'Your cart is empty. Add fresh coconut water before applying a coupon.'];
        }

        $val = Coupon::validateCoupon($code, $subtotal, $customerId, $phone, $fulfilment);
        if (!$val['valid']) {
            return ['success' => false, 'error' => $val['error']];
        }

        $coupon = $val['coupon'];
        $_SESSION[self::COUPON_SESSION_KEY] = $coupon['code'];

        $deliveryCharge = 0.00;
        if ($fulfilment === 'delivery' && $subtotal > 0) {
            $freeAbove = (float)Setting::get('free_delivery_above', '500.00');
            $deliveryCharge = ($subtotal >= $freeAbove) ? 0.00 : (float)Setting::get('delivery_charge', '40.00');
        }

        $calc = Coupon::calculateDiscount($coupon, $subtotal, $deliveryCharge);

        return [
            'success' => true,
            'message' => 'Coupon ' . $coupon['code'] . ' applied successfully!',
            'coupon' => $coupon,
            'calculation' => $calc
        ];
    }

    /**
     * Remove currently applied coupon.
     */
    public static function removeCoupon(): void {
        unset($_SESSION[self::COUPON_SESSION_KEY]);
    }

    /**
     * Get currently applied coupon and re-validate against current cart state.
     */
    public static function getAppliedCoupon(?int $customerId = null, string $phone = '', string $fulfilment = 'delivery'): ?array {
        if (empty($_SESSION[self::COUPON_SESSION_KEY])) {
            return null;
        }

        $code = $_SESSION[self::COUPON_SESSION_KEY];
        $cart = self::getCart();
        $subtotal = (float)$cart['subtotal'];

        if ($subtotal <= 0) {
            self::removeCoupon();
            return null;
        }

        $val = Coupon::validateCoupon($code, $subtotal, $customerId, $phone, $fulfilment);
        if (!$val['valid']) {
            // Invalidated due to subtotal change, etc.
            self::removeCoupon();
            return null;
        }

        $deliveryCharge = 0.00;
        if ($fulfilment === 'delivery' && $subtotal > 0) {
            $freeAbove = (float)Setting::get('free_delivery_above', '500.00');
            $deliveryCharge = ($subtotal >= $freeAbove) ? 0.00 : (float)Setting::get('delivery_charge', '40.00');
        }

        $calc = Coupon::calculateDiscount($val['coupon'], $subtotal, $deliveryCharge);
        return [
            'coupon' => $val['coupon'],
            'calculation' => $calc
        ];
    }

    public static function calculateCheckoutTotals(string $fulfilment = 'delivery', ?int $customerId = null, string $phone = ''): array {
        $cart = self::getCart();
        $subtotal = $cart['subtotal'];
        $originalDeliveryCharge = 0.00;

        if ($fulfilment === 'delivery' && $subtotal > 0) {
            $freeAbove = (float)Setting::get('free_delivery_above', '500.00');
            if ($subtotal >= $freeAbove) {
                $originalDeliveryCharge = 0.00;
            } else {
                $originalDeliveryCharge = (float)Setting::get('delivery_charge', '40.00');
            }
        }

        $discountAmount = 0.00;
        $deliveryDiscount = 0.00;
        $appliedCoupon = self::getAppliedCoupon($customerId, $phone, $fulfilment);

        if ($appliedCoupon) {
            $discountAmount = $appliedCoupon['calculation']['discount_amount'];
            $deliveryDiscount = $appliedCoupon['calculation']['delivery_discount'];
        }

        $effectiveDelivery = max(0.00, $originalDeliveryCharge - $deliveryDiscount);
        $total = max(0.00, $subtotal - $discountAmount) + $effectiveDelivery;

        return [
            'subtotal' => $subtotal,
            'original_delivery_charge' => $originalDeliveryCharge,
            'delivery_charge' => $effectiveDelivery,
            'discount_amount' => $discountAmount,
            'delivery_discount' => $deliveryDiscount,
            'total_savings' => $discountAmount + $deliveryDiscount,
            'total' => $total,
            'coupon' => $appliedCoupon['coupon'] ?? null,
            'coupon_calculation' => $appliedCoupon['calculation'] ?? null,
            'items' => $cart['items'],
            'count' => $cart['count']
        ];
    }
}
