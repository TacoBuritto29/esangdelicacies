document.addEventListener('DOMContentLoaded', () => {
    const menuButtons = document.querySelectorAll('.menu-button');
    const menuGrids = document.querySelectorAll('.menu-grid');
    const packagedMealsGrid = document.getElementById('packaged-meals-grid');
    let currentStep = 1;
    let selectedPackage = null;
    let selectedMain = null;
    let selectedVeggie = null;

    // Add event listeners to menu category buttons
    menuButtons.forEach(button => {
        button.addEventListener('click', () => {
            // Remove 'active' class from all menu buttons
            menuButtons.forEach(btn => btn.classList.remove('active'));
            // Add 'active' class to the clicked button
            button.classList.add('active');

            // Hide all menu grids
            menuGrids.forEach(grid => grid.classList.remove('active-grid'));

            // Show the corresponding grid
            const category = button.dataset.category;
            const targetGrid = document.getElementById(`${category}-grid`);
            if (targetGrid) {
                targetGrid.classList.add('active-grid');
            }
        });
    });

    // Add event listeners to bilao-grid price buttons
    const bilaoButtons = document.querySelectorAll('#bilaos-grid .bilao-button');
    bilaoButtons.forEach(button => {
        button.addEventListener('click', () => {
            const parentItem = button.closest('.menu-item');
            if (parentItem) {
                // Remove 'active-bilao-button' from all siblings
                parentItem.querySelectorAll('.bilao-button').forEach(btn => {
                    btn.classList.remove('active-bilao-button');
                });
                // Add 'active-bilao-button' to the clicked button
                button.classList.add('active-bilao-button');
                
                // Update the price display
                const newPrice = button.dataset.price;
                const priceDisplay = parentItem.querySelector('.price');
                if (priceDisplay) {
                    priceDisplay.textContent = `₱${parseFloat(newPrice).toFixed(2)}`;
                }
            }
        });
    });

    // Show the initial step for packaged meals
    const packagedMealSteps = packagedMealsGrid.querySelectorAll('.packaged-meal-step');
    packagedMealSteps.forEach((step, index) => {
        if (index === 0) {
            step.classList.add('active-step');
        } else {
            step.classList.remove('active-step');
        }
    });

    // Function to update the active step display
    const showStep = (stepNumber) => {
        packagedMealSteps.forEach(step => {
            step.classList.remove('active-step');
        });
        const targetStep = document.getElementById(`step-${stepNumber}`);
        if (targetStep) {
            targetStep.classList.add('active-step');
        }
    };

    // Handle menu tab clicks
    menuButtons.forEach(button => {
        button.addEventListener('click', () => {
            const category = button.dataset.category;

            // Remove 'active' class from all menu buttons
            menuButtons.forEach(btn => btn.classList.remove('active'));
            // Add 'active' class to the clicked menu button
            button.classList.add('active');

            // Hide all menu grids and show only the one corresponding to the clicked category
            menuGrids.forEach(grid => {
                if (grid.id === `${category}-grid`) {
                    grid.classList.add('active-grid');
                } else {
                    grid.classList.remove('active-grid');
                }
            });

            // Special handling for 'packaged-meals' to reset to step 1
            if (category === 'packaged-meals') {
                currentStep = 1;
                showStep(currentStep);
            }

            // Special handling for 'bilaos' category: reset price to 12-inch default
            if (category === 'bilaos') {
                const bilaoItems = document.querySelectorAll('#bilaos-grid .menu-item');
                bilaoItems.forEach(item => {
                    const defaultBilaoButton = item.querySelector('.bilao-button[data-size="12"]');
                    if (defaultBilaoButton) {
                        defaultBilaoButton.click(); // Simulate click to reset price and active state
                    }
                });
            }
        });
    });

    // Handle price changes for bilao items
    const bilaoItems = document.querySelectorAll('#bilaos-grid .menu-item');

    bilaoItems.forEach(item => {
        const priceDisplay = item.querySelector('.price');
        const bilaoSizeButtons = item.querySelectorAll('.bilao-button');

        bilaoSizeButtons.forEach(button => {
            button.addEventListener('click', () => {
                // Remove 'active' class from all buttons for this item
                bilaoSizeButtons.forEach(btn => btn.classList.remove('active'));
                // Add 'active' class to the clicked button
                button.classList.add('active');

                const price = button.dataset.price;
                priceDisplay.textContent = `₱${price}.00`;
            });
        });

        // Set initial price and active state for 12-inch on load for each bilao item
        const defaultBilaoButton = item.querySelector('.bilao-button[data-size="12"]');
        if (defaultBilaoButton) {
            defaultBilaoButton.click(); // Simulate click to set initial price and active state
        }
    });

    // Event listeners for packaged meals steps
    packagedMealsGrid.addEventListener('click', (e) => {
        const target = e.target.closest('.menu-item');
        if (!target) return;

        // Step 1: Package selection
        if (target.classList.contains('package-option')) {
            const packageId = target.dataset.package;
            selectedPackage = packageId;
            currentStep = 2;
            showStep(currentStep);
        }

        // Step 2: Main dish selection
        else if (target.classList.contains('main-option')) {
            selectedMain = target.dataset.main;
            currentStep = 3;
            showStep(currentStep);
        }

        // Step 3: Veggie selection
        else if (target.classList.contains('veggie-option')) {
            selectedVeggie = target.dataset.veggie;
            // Highlight the selected veggie option
            document.querySelectorAll('.veggie-option').forEach(item => item.classList.remove('selected'));
            target.classList.add('selected');
        }
    });

    // Event listener for the "Add" button
    packagedMealsGrid.addEventListener('click', (e) => {
        if (e.target.classList.contains('add-button')) {
            if (selectedPackage && selectedMain && selectedVeggie) {
                alert(`Order Added: Package ${selectedPackage} with Main: ${selectedMain} and Veggie: ${selectedVeggie}`);
                // Here, you would typically add the order to a cart or process it further
                // Reset selections and return to step 1
                selectedPackage = null;
                selectedMain = null;
                selectedVeggie = null;
                currentStep = 1;
                showStep(currentStep);
            } else {
                alert('Please complete all steps to add the meal.');
            }
        }
    });
});