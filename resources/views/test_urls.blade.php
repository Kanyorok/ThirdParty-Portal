<!DOCTYPE html>
<html>
<head>
    <title>URL Test</title>
</head>
<body>
    <h1>Generated URLs Test</h1>
    <pre>
Suppliers URL: {{ url('procurement/purchase-order/prequalified-suppliers/0') }}
RFQ Items URL: {{ url('procurement/purchase-order/rfq-items/1') }}
Direct Plans URL: {{ url('procurement/purchase-order/direct-plans') }}
Root Categories URL: {{ url('procurement/purchase-order/root-categories') }}
    </pre>
    
    <h2>Test AJAX Calls</h2>
    <button onclick="testSuppliers()">Test Suppliers</button>
    <button onclick="testRFQItems()">Test RFQ Items</button>
    <div id="results"></div>
    
    <script>
        function testSuppliers() {
            fetch(`{{ url('procurement/purchase-order/prequalified-suppliers/0') }}`)
                .then(r => r.json())
                .then(data => {
                    document.getElementById('results').innerHTML = 
                        '<h3>Suppliers Result:</h3><pre>' + JSON.stringify(data, null, 2) + '</pre>';
                })
                .catch(err => {
                    document.getElementById('results').innerHTML = 
                        '<h3>Suppliers Error:</h3><pre>' + err.message + '</pre>';
                });
        }
        
        function testRFQItems() {
            fetch(`{{ url('procurement/purchase-order/rfq-items/1') }}`)
                .then(r => r.json())
                .then(data => {
                    document.getElementById('results').innerHTML = 
                        '<h3>RFQ Items Result:</h3><pre>' + JSON.stringify(data, null, 2) + '</pre>';
                })
                .catch(err => {
                    document.getElementById('results').innerHTML = 
                        '<h3>RFQ Items Error:</h3><pre>' + err.message + '</pre>';
                });
        }
    </script>
</body>
</html>
