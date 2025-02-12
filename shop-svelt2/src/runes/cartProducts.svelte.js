const cartProducts = $state([]);

export function createProductsCart() {
	return {
		get cartProducts() {
			return cartProducts;
		},
		get countProductsInCart() {
			return cartProducts.length;
		},
		get totalPrice() {
			return cartProducts.reduce((totalPrice, { price }) => (totalPrice += price), 0).toFixed(2);
		},
		deleteProductFromCart: (index) => cartProducts.slice(index, 1),
		addProductToCart: (product) => cartProducts.push(product)
	};
}
