@extends('layouts.app')
@section('title', 'Fleet Procurement And Disposal')
@section('content')


<div class="container mt-5">
        <h1 class="text-center text-success">Create Spare Parts Inventory</h1>

        <!-- Form to Add New Spare Part -->
        <section id="add-spare-part" class="mt-4">
            <h2>Add New Spare Part</h2>
            <form method="POST" action="create_inventory.php">
                <div class="mb-3">
                    <label for="name" class="form-label">Part Name</label>
                    <input type="text" class="form-control" id="name" name="name" required>
                </div>
                <div class="mb-3">
                    <label for="quantity" class="form-label">Quantity</label>
                    <input type="number" class="form-control" id="quantity" name="quantity" required>
                </div>
                <div class="mb-3">
                    <label for="reorder_level" class="form-label">Reorder Level</label>
                    <input type="number" class="form-control" id="reorder_level" name="reorder_level" required>
                </div>
                <div class="mb-3">
                    <label for="vendor" class="form-label">Vendor</label>
                    <input type="text" class="form-control" id="vendor" name="vendor" required>
                </div>
                <button type="submit" class="btn btn-primary">Add Spare Part</button>
            </form>
        </section>

        <!-- Displaying Spare Parts List -->
        <section id="spare-parts-list" class="mt-5">
            <h2>Spare Parts Inventory</h2>
            <?php if (!empty($spareParts)): ?>
                <table class="table table-striped">
                    <thead class="thead-dark">
                        <tr>
                            <th>ID</th>
                            <th>Part Name</th>
                            <th>Quantity</th>
                            <th>Reorder Level</th>
                            <th>Vendor</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($spareParts as $part): ?>
                            <tr>
                                <td><?php echo $part['id']; ?></td>
                                <td><?php echo $part['name']; ?></td>
                                <td><?php echo $part['quantity']; ?></td>
                                <td><?php echo $part['reorder_level']; ?></td>
                                <td><?php echo $part['vendor']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="alert alert-info">No spare parts added yet. Please add spare parts above.</p>
            <?php endif; ?>
        </section>

    </div>


@endsection