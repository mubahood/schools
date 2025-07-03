<div class="stock-summary-grid">
    <div class="card">
        <h5>Total Categories</h5>
        <p>{{ number_format($totalCategories) }}</p>
    </div>
    <div class="card">
        <h5>Total Batches</h5>
        <p>{{ number_format($totalBatches) }}</p>
    </div>
    <div class="card">
        <h5>Current Quantity</h5>
        <p>{{ number_format($totalQty) }}</p>
    </div>
    <div class="card">
        <h5>Total Stock Value (UGX)</h5>
        <p>{{ number_format($totalValue) }}</p>
    </div>
    <div class="card">
        <h5>Out-of-Stock Categories</h5>
        <p>{{ number_format($outOfStockCount) }}</p>
    </div>
    <div class="card">
        <h5>Low-Stock Categories</h5>
        <p>{{ number_format($lowStockCount) }}</p>
    </div>
</div>

<style>
    .stock-summary-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .stock-summary-grid .card {
        background: #fff;
        border-radius: 8px;
        padding: 1rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        text-align: center;
    }

    .stock-summary-grid .card h5 {
        margin: 0 0 0.5rem;
        font-size: 0.95rem;
        color: #333;
        font-weight: 500;
    }

    .stock-summary-grid .card p {
        margin: 0;
        font-size: 1.4rem;
        font-weight: 600;
        color: #111;
    }
</style>
