const cartProducts = $state([]);

export function createProductsCart() {
	return {
		get cartProducts() {
			return cartProducts;
		},
		get countProductsInCart() {
			return cartProducts.reduce((acc, item) => acc + (item.count || 1), 0);
		},
		get totalPrice() {
			const total = cartProducts.reduce((sum, item) => {
				const itemCount = item.count || 1;
				return sum + item.price * itemCount;
			}, 0);
			return total.toFixed(2);
		},
		deleteProductFromCart(index) {
			cartProducts.splice(index, 1);
		},
		addProductToCart(product) {
			const existing = cartProducts.find((p) => p.id === product.id);
			if (existing) {
				existing.count = (existing.count || 1) + (product.count || 1);
			} else {
				cartProducts.push({
					...product,
					count: product.count || 1,
				});
			}
		},

		// Добавляем новые методы:
		plusProductFromCart(index) {
			cartProducts[index].count++;
		},
		minusProductFromCart(index) {
			if (cartProducts[index].count > 1) {
				cartProducts[index].count--;
			} else {
				// Если count становится 0, удаляем товар из корзины
				cartProducts.splice(index, 1);
			}
		},
	};
}
