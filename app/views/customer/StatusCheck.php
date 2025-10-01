<?php
$conn = new mysqli("localhost", "root", "", "ESANG_DB");

// Assuming you have MENU table
$result = $conn->query("SELECT menuId, name, stock FROM MENU");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Cashier Dashboard</title>
    <style>
        .available {
            color: green;
            font-weight: bold;
        }
        .unavailable {
            color: red;
            font-weight: bold;
        }
        table {
            border-collapse: collapse;
            width: 60%;
        }
        th, td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: center;
        }
    </style>
</head>
<body>
    <h1>Cashier Menu</h1>
    <table>
        <tr>
            <th>Menu ID</th>
            <th>Item</th>
            <th>Stock</th>
            <th>Status</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()) { ?>
            <tr>
                <td><?php echo $row['menuId']; ?></td>
                <td><?php echo $row['name']; ?></td>
                <td><?php echo $row['stock']; ?></td>
                <td>
                    <?php if ($row['stock'] > 0) { ?>
                        <span class="available">Available</span>
                    <?php } else { ?>
                        <span class="unavailable">Unavailable</span>
                    <?php } ?>
                </td>
            </tr>
        <?php } ?>
    </table>
</body>
</html>
