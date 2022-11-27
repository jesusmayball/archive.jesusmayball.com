<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="/favicon.png">

    <title>Merch Store - Jesus May Ball</title>

    <link href="https://jesusmayball.com/css/baseline.css" type="text/css" rel="stylesheet" />
    <link href="css/flickity.css" type="text/css" rel="stylesheet" />
    <link href="css/main.css" type="text/css" rel="stylesheet" />

    <!-- Load Stripe.js on your website. -->
    <script src="https://js.stripe.com/v3/"></script>
    <script src="js/flickity.js"></script>
    <script src="js/carousels.js"></script>
</head>

<body>
    <script>
        let basket = [];

        const price_id_map = new Map();

        function transform_basket_item(item) {
            const price_id = item["size"] ?
                price_id_map.get(item["id"])[item["size"]] :
                price_id_map.get(item["id"]);
            let item_ = {
                ...item,
                "id": price_id
            };
            return item_;
        }

        function updateCheckoutButton() {
            const checkoutButton = document.querySelector("#checkout-button");
            const quantIcon = document.querySelector("#checkout-button-quantity");
            const els = basket.length;
            if (els > 0) {
                quantIcon.innerHTML = els;
                quantIcon.classList.add("show");
                checkoutButton.classList.remove("hide");
            } else {
                checkoutButton.classList.add("hide");
                quantIcon.classList.remove("show");
            }
        }

        function addItemToBasket(itemId, size = null) {
            const item = {
                "id": itemId,
                "quantity": 1
            };
            if (size) {
                item["size"] = size;
            }
            basket.push(item);
            updateCheckoutButton();
        }

        function removeItemFromBasket(itemId) {
            basket = basket.filter((item) => item["id"] !== itemId);
            updateCheckoutButton();
        }

        function updateItemQuantity(itemId, quantity) {
            const item = basket.find((item) => item["id"] === itemId);
            item["quantity"] = quantity;
            updateCheckoutButton();
        }

        function updateItemSize(itemId, size) {
            const item = basket.find((item) => item["id"] === itemId);
            if (item) {
                item["size"] = size;
            }
        }

        function buyItem(itemId, size = null) {
            const itemEl = document.querySelector(`#item-${itemId}`);
            itemEl.classList.add("in-basket");
            addItemToBasket(itemId, size);
        }

        function removeItem(itemId) {
            const itemEl = document.querySelector(`#item-${itemId}`);
            itemEl.classList.remove("in-basket");
            removeItemFromBasket(itemId);

            const quantityEl = document.querySelector(`#item-${itemId} #item-quantity`);
            quantityEl.value = 1;
        }

        function onInputChange(quantity, itemId) {
            const itemEl = document.querySelector(`#item-${itemId}`);
            quantity = Number.parseInt(quantity);
            updateItemQuantity(itemId, quantity);
        }

        function checkout() {
            let basket_map = basket.map((item) => transform_basket_item(item));
            if (basket.length <= 0) {
                console.error("Cannot checkout basket with 0 items.")
            } else {
                console.log(basket_map);
                const basketDataInput = document.querySelector("#basket-data-input");
                basketDataInput.value = JSON.stringify(basket_map);
                const basketForm = document.querySelector("#basket-form");
                basketForm.submit();
            }
        }
    </script>

    <div class="page">
        <nav>
            <a href="/">← Home</a>
        </nav>
        <h1>Merch Store</h1>
        <p>Purchase some Jesus College May Ball stash. All items will be available to pick up from Jesus College in June. Details will be emailed to you and posted on socials when items are available for collection.</p>
        <hr>
        <section>
            <h2>Available items</h2>
            <div class="items-container">
                <?php

                require './vendor/autoload.php';

                $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
                $dotenv->load();

                $stripe_client = new \Stripe\StripeClient($_ENV['STRIPE_SECRET_KEY']);

                $prices = $stripe_client->prices->all(["limit" => 100, "active" => true]);
                $products = array();
                foreach ($prices as $price) {
                    $product = $stripe_client->products->retrieve(($price["product"]));
                    if (isset($products[$product["id"]])) {
                        $product_ = $products[$product["id"]];
                        $size = $price["metadata"]["size"];
                        $sizes_ = $product_["sizes"];
                        $sizes_[$size] = $price["id"];
                        $product_["sizes"] = $sizes_;
                        $products[$product["id"]] = $product_;
                    } else {
                        $product_ = array(
                            "id" => $product["id"],
                            "unit_amount" => $price["unit_amount"],
                            "price_id" => $price["id"],
                            "name" => $product["name"],
                            "description" => $product["description"],
                            "images" => $product["images"],
                        );
                        if (isset($price["metadata"]["size"])) {
                            $size = $price["metadata"]["size"];
                            $product_["sizes"] = array();
                            $product_["sizes"][$size] = $price["id"];
                        }
                        $products[$product["id"]] = $product_;
                    }
                }

                echo '<script>';
                foreach ($products as $product) {
                    if (isset($product["sizes"])) {
                        echo 'price_id_map.set(\'' . $product["id"] . '\',' . json_encode($product["sizes"]) . ');';
                    } else {
                        echo 'price_id_map.set("' . $product["id"] . '", "' . $product["price_id"] . '");';
                    }
                }
                echo '</script>';
                foreach ($products as $product) {
                    $carousel_cls = sizeof($product["images"]) > 1 ? "main-carousel" : "";
                    $sizes_html = "";
                    unset($default_size);
                    if (isset($product["sizes"])) {
                        $keys = array_keys($product["sizes"]);
                        $default_size = array_pop($keys);
                        $sizes_html =
                            '<label> Size: </label><select id="item-size" onchange="updateItemSize(\'' . $product["id"] . '\', this.value)">' .
                            implode(
                                "\n",
                                array_map(
                                    fn ($size, $price_id): string => '<option value="' . $size . '" ' . ($size == $default_size ? 'selected ' : ' ') . '>' . $size . '</option>',
                                    array_keys($product["sizes"]),
                                    array_values($product["sizes"])
                                )
                            ) .
                            '</select>';
                    }
                    echo '<div class="store-item" id="item-' . $product["id"] . '">
                    <h3 class="store-item-name">' . $product["name"] . '</h3>
                    <div class="store-item-image-price-container">
                        <div class="store-item-price">
                            &pound;' . ($product["unit_amount"] / 100) . '
                        </div>
                        <div class="' . $carousel_cls . '">'
                        .
                        implode(
                            "\n",
                            array_map(
                                fn ($src) =>
                                '<div class="carousel-cell store-item-carousel">
                                        <img class="store-item-image" src="' . $src . '"/>
                                    </div>',
                                $product["images"]
                            )
                        )
                        .
                        '</div>
                        </div>
                    <p class="store-item-description">' . str_replace("\n", "<br>", $product["description"])  . '
                    </p>
                    <div class="store-item-purchase">
                        <button class="item-buy" onclick="buyItem(\'' . $product["id"] . '\'';
                    if (isset($default_size)) {
                        echo ', \'';
                        echo $default_size;
                        echo '\'';
                    }
                    echo ')">
                            Add to basket
                        </button>
                        <div class="item-quantity">
                            <label>Quantity:</label>
                            <input id="item-quantity" value="1" type="number" min="1" max="10" oninput="onInputChange(this.value, \'' . $product["id"] . '\')" />'
                        .
                        $sizes_html
                        .
                        '<button class="item-remove" onclick="removeItem(\'' . $product["id"]  . '\')">
                                Remove
                            </button>
                        </div>
                    </div>
                </div>';
                }
                ?>

            </div>
            <form id="basket-form" action="./create-checkout-session.php" method="POST">
                <button id="checkout-button" class="hide" onclick="checkout()" type="button">
                    <div id="checkout-button-quantity">0</div>
                    Checkout
                </button>
                <input id="basket-data-input" type="hidden" name="basket-data" value="" />
            </form>
        </section>
        <div id="error-message"></div>
    </div>
</body>

</html>