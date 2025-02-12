<script>
	import { createProductsCart } from "../runes/cartProducts.svelte";
	const { addProductToCart } = createProductsCart();
	// import { cartProducts } from "../runes/cartProducts.svelte";
	let data = { products: [] };

	let searchValue = '';
	let categorysList = [];
	let selectedCategoryName;
	const categoryFilterNAme = 'Category';
	let loading = false;
	let isOpenModal = false;

	function fetchProducts(url) {
		loading = true;
		fetch(url)
			.then((res) => res.json())
			.then((enterData) => {
				if (!enterData.products.length) {
					isOpenModal = true;
				} else {
					data.products = enterData.products;
				}
				loading = false;
			});
	}

	$: if (searchValue === '') {
		fetchProducts('https://dummyjson.com/products');
	}
   
   function searchFunction() {
		if (searchValue) {
			fetchProducts(`https://dummyjson.com/products/search?q=${searchValue}`)
		} else if (selectedCategoryName !== categoryFilterNAme) {
			getProductsByCategory(selectedCategoryName);
		}
	}

	function keypressEnter(value) {
		if (value.key === 'Enter') {
			searchFunction();
		}
	}

	// Get product category List
	(function getProductCategoryList() {
		fetch('https://dummyjson.com/products/category-list')
		.then(res => res.json())
		.then(function (categories) {
			categorysList = categories;
		});
	})();

	function getProductsByCategory(categoryName) {
		loading = true;
		fetch(`https://dummyjson.com/products/category/${categoryName}`)
			.then(res => res.json())
			.then((enterData) => {
				data.products = enterData.products;
				loading = false;
			});
	}

	function clearAllFilters() {
		searchValue = '';
		selectedCategoryName = categoryFilterNAme;
		fetchProducts('https://dummyjson.com/products');
	}

</script>

<div class="join flex justify-center">
	<div>
	  <div>
		<input class="input input-bordered join-item" placeholder="Search" bind:value={searchValue} onkeypress={keypressEnter} />
	  </div>
	</div>
	<select class="select select-bordered join-item" bind:value={selectedCategoryName}>
	  <option disabled selected>{categoryFilterNAme}</option>
	  {#each categorysList as categoryName}
	  	<option value={categoryName}>{categoryName}</option>
	  {/each}
	</select>
	<button class="btn join-item" disabled={loading} onclick={searchFunction}>
		{#if loading}
			<span class="loading loading-spinner text-info"></span>
		{/if}
		Search</button>
	{#if searchValue || selectedCategoryName !== categoryFilterNAme}
	<button class="btn btn-info join-item" onclick={clearAllFilters}>Clear filter</button>
	{/if}
	
</div>

<dialog class="modal" open={isOpenModal}>
  <div class="modal-box">
    <h3 class="text-lg font-bold">Warning!</h3>
    <p class="py-4">По запиту "{searchValue}" нічого не знайдено</p>
    <div class="modal-action">
      <form method="dialog">
        <button class="btn" onclick={ () => (isOpenModal = false) }>Close</button>
      </form>
    </div>
  </div>
</dialog>

<div class="overflow-x-auto">
	<table class="table">
		<!-- head -->
		<thead>
			<tr>
				<th>
					<label>
						<input type="checkbox" class="checkbox" />
					</label>
				</th>
				<th>Product</th>
				<th>Description</th>
				<th>Price</th>
				<th></th>
			</tr>
		</thead>
		<tbody>
			<!-- row 1 -->
			{#each data.products as product}
				<tr>
					<th>
						<label>
							<input type="checkbox" class="checkbox" />
						</label>
					</th>
					<td>
						<div class="flex items-center gap-3">
							<div class="avatar">
								<div class="mask mask-squircle h-12 w-12">
									<img src={product.thumbnail} alt="Avatar Tailwind CSS Component" />
								</div>
							</div>
							<div>
								<div class="font-bold">{product.title}</div>
								<div class="text-sm opacity-50">{product.category}</div>
							</div>
						</div>
					</td>
					<td>
						{product.description}
						<br />
						<span class="badge badge-ghost badge-sm">{product.warrantyInformation}</span>
					</td>
					<td><strong>{product.price} $</strong></td>
					<th>
						<button 
							class="btn"
							onclick={() => {
								addProductToCart(product);
								}}
						>
							<svg
								class="w-10 h-10"
								viewBox="0 0 24 24"
								fill="none"
								xmlns="http://www.w3.org/2000/svg"
							>
								<path
									d="M7.5 18C8.32843 18 9 18.6716 9 19.5C9 20.3284 8.32843 21 7.5 21C6.67157 21 6 20.3284 6 19.5C6 18.6716 6.67157 18 7.5 18Z"
									stroke="#0000FF"
									stroke-width="1.5"
								/>
								<path
									d="M16.5 18.0001C17.3284 18.0001 18 18.6716 18 19.5001C18 20.3285 17.3284 21.0001 16.5 21.0001C15.6716 21.0001 15 20.3285 15 19.5001C15 18.6716 15.6716 18.0001 16.5 18.0001Z"
									stroke="#0000FF"
									stroke-width="1.5"
								/>
								<path
									d="M2 3L2.26121 3.09184C3.5628 3.54945 4.2136 3.77826 4.58584 4.32298C4.95808 4.86771 4.95808 5.59126 4.95808 7.03836V9.76C4.95808 12.7016 5.02132 13.6723 5.88772 14.5862C6.75412 15.5 8.14857 15.5 10.9375 15.5H12M16.2404 15.5C17.8014 15.5 18.5819 15.5 19.1336 15.0504C19.6853 14.6008 19.8429 13.8364 20.158 12.3075L20.6578 9.88275C21.0049 8.14369 21.1784 7.27417 20.7345 6.69708C20.2906 6.12 18.7738 6.12 17.0888 6.12H11.0235M4.95808 6.12H7"
									stroke="#0000FF"
									stroke-width="1.5"
									stroke-linecap="round"
								/>
							</svg>
						</button>
					</th>
				</tr>
			{/each}
		</tbody>
		<!-- foot -->
		<tfoot>
			<tr>
				<th></th>
				<th>Product</th>
				<th>Description</th>
				<th>Price</th>
				<th></th>
			</tr>
		</tfoot>
	</table>
</div>

<!-- <h1>Welcome to SvelteKit</h1>
<p>Visit <a href="https://kit.svelte.dev">kit.svelte.dev</a> to read the documentation</p>
<strong>20240913 08:44 Test OK</strong>
<br>
<a class="btn btn-primary" href="/contacts">Contacts</a> -->
