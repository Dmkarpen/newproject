<script>
  import { onMount } from 'svelte';
  import { page } from '$app/stores';
  import { get } from 'svelte/store';
  import { allProducts } from '$lib/stores/products.js'; // <-- импорт store

  let product = null;

  onMount(async () => {
    // 1. Берём ID из URL /product/[id]
    const { id } = $page.params;
    const idNum = parseInt(id, 10);

    // 2. Сначала пытаемся найти товар в глобальном store
    const products = get(allProducts);
    product = products.find(p => p.id === idNum);

    // 3. Если не нашли — делаем fetch (прямой переход по ссылке)
    if (!product) {
      const response = await fetch(`http://127.0.0.1:8000/api/products/${id}`);
      product = await response.json();
    }
  });
</script>

<!-- Оформим страницу -->
<div class="max-w-xl mx-auto p-4">
  {#if product}
    <h1 class="text-2xl font-bold text-center mb-4">{product.title}</h1>
    <p class="text-center mb-4">{product.description}</p>

    <!-- Проверяем, есть ли массив картинок -->
    {#if product.images && product.images.length > 0}
      <!-- Основная карусель -->
      <div class="carousel w-full mb-4">
        {#each product.images as image, i}
          <div id={"item" + (i + 1)} class="carousel-item w-full">
            <img
              src={image.url}
              alt="product image"
              class="w-full object-cover"
              style="max-height: 400px;"
            />
          </div>
        {/each}
      </div>

      <!-- Индикаторы (кнопки для переключения) -->
      <div class="flex w-full justify-center gap-2">
        {#each product.images as _, i}
          <a href={"#item" + (i + 1)} class="btn btn-xs">{i + 1}</a>
        {/each}
      </div>
    {:else}
      <p class="text-center text-gray-500">No additional images.</p>
    {/if}

    <p class="mt-4 font-semibold text-center">
      Price: {product.price} $
    </p>
  {/if}
</div>
