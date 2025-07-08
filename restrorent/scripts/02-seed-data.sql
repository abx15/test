-- Insert sample data
USE restaurant_website;

-- Insert default admin user (password: admin123)
INSERT INTO admin_users (username, email, password, role) VALUES 
('admin', 'admin@restaurant.com', 'admin123', 'admin');

-- Insert menu categories
INSERT INTO menu_categories (name, slug, description, display_order) VALUES 
('Starters', 'starters', 'Delicious appetizers to start your meal', 1),
('Main Course', 'main-course', 'Our signature main dishes', 2),
('Desserts', 'desserts', 'Sweet treats to end your meal', 3),
('Drinks', 'drinks', 'Refreshing beverages and cocktails', 4);

-- Insert sample menu items
INSERT INTO menu_items (name, description, price, category_id, is_featured, ingredients, allergens, display_order) VALUES 
-- Starters
('Caesar Salad', 'Fresh romaine lettuce with parmesan cheese, croutons and our signature Caesar dressing', 12.99, 1, TRUE, 'Romaine lettuce, Parmesan cheese, Croutons, Caesar dressing', 'Dairy, Gluten', 1),
('Buffalo Wings', 'Crispy chicken wings tossed in spicy buffalo sauce, served with celery and blue cheese', 14.99, 1, FALSE, 'Chicken wings, Buffalo sauce, Celery, Blue cheese dressing', 'Dairy', 2),
('Mozzarella Sticks', 'Golden fried mozzarella cheese sticks served with marinara sauce', 9.99, 1, FALSE, 'Mozzarella cheese, Breadcrumbs, Marinara sauce', 'Dairy, Gluten', 3),
('Garlic Bread', 'Toasted bread with garlic butter and herbs', 7.99, 1, FALSE, 'Bread, Garlic, Butter, Herbs', 'Dairy, Gluten', 4),

-- Main Course
('Grilled Salmon', 'Fresh Atlantic salmon grilled to perfection, served with roasted vegetables and rice', 24.99, 2, TRUE, 'Atlantic salmon, Mixed vegetables, Rice, Lemon', 'Fish', 1),
('Ribeye Steak', 'Premium 12oz ribeye steak cooked to your preference, served with mashed potatoes', 32.99, 2, TRUE, 'Ribeye steak, Mashed potatoes, Seasonal vegetables', 'Dairy', 2),
('Chicken Parmesan', 'Breaded chicken breast topped with marinara sauce and melted cheese, served with pasta', 19.99, 2, FALSE, 'Chicken breast, Marinara sauce, Mozzarella cheese, Pasta', 'Dairy, Gluten', 3),
('Vegetarian Pasta', 'Fresh pasta with seasonal vegetables in a creamy herb sauce', 16.99, 2, FALSE, 'Pasta, Mixed vegetables, Cream sauce, Herbs', 'Dairy, Gluten', 4),
('Fish and Chips', 'Beer-battered cod served with crispy fries and tartar sauce', 18.99, 2, FALSE, 'Cod fish, Potatoes, Beer batter, Tartar sauce', 'Fish, Gluten', 5),

-- Desserts
('Chocolate Lava Cake', 'Warm chocolate cake with molten center, served with vanilla ice cream', 8.99, 3, TRUE, 'Chocolate, Flour, Eggs, Vanilla ice cream', 'Dairy, Eggs, Gluten', 1),
('Tiramisu', 'Classic Italian dessert with coffee-soaked ladyfingers and mascarpone cream', 7.99, 3, FALSE, 'Ladyfingers, Coffee, Mascarpone, Cocoa powder', 'Dairy, Eggs, Gluten', 2),
('New York Cheesecake', 'Rich and creamy cheesecake with graham cracker crust', 6.99, 3, FALSE, 'Cream cheese, Graham crackers, Eggs', 'Dairy, Eggs, Gluten', 3),
('Ice Cream Sundae', 'Three scoops of vanilla ice cream with chocolate sauce and whipped cream', 5.99, 3, FALSE, 'Vanilla ice cream, Chocolate sauce, Whipped cream', 'Dairy', 4),

-- Drinks
('Fresh Orange Juice', 'Freshly squeezed orange juice', 4.99, 4, FALSE, 'Fresh oranges', '', 1),
('Coffee', 'Premium roasted coffee beans', 3.99, 4, FALSE, 'Coffee beans', '', 2),
('Coca Cola', 'Classic Coca Cola', 2.99, 4, FALSE, 'Coca Cola', '', 3),
('House Wine', 'Our selection of red or white wine', 8.99, 4, FALSE, 'Wine grapes', 'Sulfites', 4),
('Craft Beer', 'Local craft beer selection', 6.99, 4, FALSE, 'Hops, Malt, Yeast', 'Gluten', 5),
('Sparkling Water', 'Premium sparkling water', 3.99, 4, FALSE, 'Sparkling water', '', 6);

-- Insert restaurant settings
INSERT INTO restaurant_settings (setting_key, setting_value) VALUES 
('restaurant_name', 'Delicious Bites'),
('restaurant_phone', '+1 (555) 123-4567'),
('restaurant_email', 'info@deliciousbites.com'),
('restaurant_address', '123 Food Street, Culinary City, CC 12345'),
('opening_hours', 'Mon-Thu: 11:00 AM - 10:00 PM, Fri-Sat: 11:00 AM - 11:00 PM, Sun: 12:00 PM - 9:00 PM'),
('delivery_fee', '5.99'),
('minimum_order', '25.00');
