<script>
    import { createProductsCart } from "../../runes/cartProducts.svelte";
    const { cartProducts, totalPrice, deleteProductFromCart } = createProductsCart();
</script>

<div style="text-align:center;">
    <h1>Welcome to Cart Producrs</h1>
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
              <th>Name</th>
              <th>Job</th>
              <th>Favorite Color</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            {#each cartProducts as product, index}
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
                        <img
                          src={product.thumbnail}
                          alt={product.title} />
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
                    class="btn" onclick={() => deleteProductFromCart(index)}
                >
                <svg class="v-10 h-10" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M4 7H20" stroke="#ff0000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M6 7V18C6 19.6569 7.34315 21 9 21H15C16.6569 21 18 19.6569 18 18V7" stroke="#ff0000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M9 5C9 3.89543 9.89543 3 11 3H13C14.1046 3 15 3.89543 15 5V7H9V5Z" stroke="#ff0000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
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
              <th></th>              
              <th class="text-right">Total</th>
              <th>${totalPrice}</th>
              <th></th>
            </tr>
          </tfoot>
        </table>
      </div>
</div>