/**
 * Multi-select moderne pour les filtres de carte
 * Pour Tracteur Zone
 */

class ModernMultiSelect {
    constructor(selector, options = {}) {
        this.options = {
            placeholder: "Sélectionner des options...",
            searchPlaceholder: "Rechercher...",
            selectedItemsText: "options sélectionnées",
            selectAllText: "Tout sélectionner",
            clearAllText: "Tout effacer",
            applyText: "Appliquer",
            showSearch: true,
            showActions: true,
            showTags: false,
            maxTagsVisible: 3,
            ...options
        };

        this.container = document.querySelector(selector);
        if (!this.container) {
            console.error(`Element "${selector}" not found`);
            return;
        }

        this.selectElement = this.container.querySelector('select');
        if (!this.selectElement) {
            console.error(`No select element found inside "${selector}"`);
            return;
        }

        this.selectElement.style.display = 'none';
        this.selectedOptions = new Set();
        this.initSelectedOptions();
        this.render();
        this.setupEventListeners();
    }

    initSelectedOptions() {
        Array.from(this.selectElement.options).forEach(option => {
            if (option.selected) {
                this.selectedOptions.add(option.value);
            }
        });
    }

    render() {
        // Create the multiselect container
        this.multiSelectContainer = document.createElement('div');
        this.multiSelectContainer.className = 'multi-select-container';

        // Create header
        this.header = document.createElement('div');
        this.header.className = 'multi-select-header';
        
        const titleSpan = document.createElement('span');
        titleSpan.className = 'multi-select-title';
        titleSpan.textContent = this.selectElement.getAttribute('data-label') || 
                              this.selectElement.getAttribute('aria-label') || 
                              this.getLabelText();
        
        const placeholderSpan = document.createElement('span');
        placeholderSpan.className = 'multi-select-placeholder';
        
        const headerTexts = document.createElement('div');
        headerTexts.style.display = 'flex';
        headerTexts.style.flexDirection = 'column';
        headerTexts.appendChild(titleSpan);
        headerTexts.appendChild(placeholderSpan);
        
        const icon = document.createElement('span');
        icon.className = 'icon';
        icon.innerHTML = '<svg width="14" height="8" viewBox="0 0 14 8" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M1 1L7 7L13 1" stroke="#2A2A2A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
        
        this.header.appendChild(headerTexts);
        this.header.appendChild(icon);

        // Create dropdown
        this.dropdown = document.createElement('div');
        this.dropdown.className = 'multi-select-dropdown';

        // Add search if enabled
        if (this.options.showSearch) {
            const searchDiv = document.createElement('div');
            searchDiv.className = 'multi-select-search';
            
            this.searchInput = document.createElement('input');
            this.searchInput.type = 'text';
            this.searchInput.placeholder = this.options.searchPlaceholder;
            
            searchDiv.appendChild(this.searchInput);
            this.dropdown.appendChild(searchDiv);
        }

        // Create options container
        this.optionsContainer = document.createElement('div');
        this.optionsContainer.className = 'multi-select-options';

        // Add options
        this.renderOptions();

        // Add actions if enabled
        if (this.options.showActions) {
            const actionsDiv = document.createElement('div');
            actionsDiv.className = 'multi-select-actions';
            
            const selectAllBtn = document.createElement('button');
            selectAllBtn.type = 'button';
            selectAllBtn.className = 'select-all-btn';
            selectAllBtn.textContent = this.options.selectAllText;
            
            const clearAllBtn = document.createElement('button');
            clearAllBtn.type = 'button';
            clearAllBtn.className = 'clear-all-btn';
            clearAllBtn.textContent = this.options.clearAllText;
            
            const applyBtn = document.createElement('button');
            applyBtn.type = 'button';
            applyBtn.className = 'apply-btn';
            applyBtn.textContent = this.options.applyText;
            
            actionsDiv.appendChild(selectAllBtn);
            actionsDiv.appendChild(clearAllBtn);
            actionsDiv.appendChild(applyBtn);
            
            this.dropdown.appendChild(actionsDiv);
        }

        this.dropdown.appendChild(this.optionsContainer);

        // Tags container
        if (this.options.showTags) {
            this.tagsContainer = document.createElement('div');
            this.tagsContainer.className = 'multi-select-tags';
            this.multiSelectContainer.appendChild(this.tagsContainer);
        }

        // Add everything to the main container
        this.multiSelectContainer.appendChild(this.header);
        this.multiSelectContainer.appendChild(this.dropdown);

        // Replace the select with our custom multiselect
        this.container.appendChild(this.multiSelectContainer);

        this.updatePlaceholder();
    }

    renderOptions() {
        this.optionsContainer.innerHTML = '';
        const fragment = document.createDocumentFragment();
        const options = Array.from(this.selectElement.options);
        
        options.forEach(option => {
            if (option.value) {
                const optionDiv = document.createElement('div');
                optionDiv.className = 'multi-select-option';
                if (this.selectedOptions.has(option.value)) {
                    optionDiv.classList.add('selected');
                }
                
                const checkbox = document.createElement('input');
                checkbox.type = 'checkbox';
                checkbox.checked = this.selectedOptions.has(option.value);
                checkbox.value = option.value;
                
                const label = document.createElement('label');
                label.textContent = option.textContent;
                
                optionDiv.appendChild(checkbox);
                optionDiv.appendChild(label);
                
                optionDiv.addEventListener('click', (e) => {
                    const checkbox = optionDiv.querySelector('input[type="checkbox"]');
                    checkbox.checked = !checkbox.checked;
                    
                    if (checkbox.checked) {
                        this.selectedOptions.add(checkbox.value);
                        optionDiv.classList.add('selected');
                    } else {
                        this.selectedOptions.delete(checkbox.value);
                        optionDiv.classList.remove('selected');
                    }
                    
                    this.updateSelectElement();
                    this.updatePlaceholder();
                    
                    if (this.options.showTags) {
                        this.renderTags();
                    }
                    
                    e.stopPropagation();
                });
                
                fragment.appendChild(optionDiv);
            }
        });
        
        this.optionsContainer.appendChild(fragment);
    }

    setupEventListeners() {
        // Toggle dropdown on header click
        this.header.addEventListener('click', () => {
            this.toggleDropdown();
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => {
            if (!this.multiSelectContainer.contains(e.target)) {
                this.closeDropdown();
            }
        });

        // Search functionality
        if (this.options.showSearch) {
            this.searchInput.addEventListener('input', () => {
                const searchTerm = this.searchInput.value.toLowerCase();
                const options = this.optionsContainer.querySelectorAll('.multi-select-option');
                
                options.forEach(option => {
                    const label = option.querySelector('label').textContent.toLowerCase();
                    if (label.includes(searchTerm)) {
                        option.style.display = '';
                    } else {
                        option.style.display = 'none';
                    }
                });
            });
        }

        // Action buttons
        if (this.options.showActions) {
            const selectAllBtn = this.dropdown.querySelector('.select-all-btn');
            const clearAllBtn = this.dropdown.querySelector('.clear-all-btn');
            const applyBtn = this.dropdown.querySelector('.apply-btn');
            
            selectAllBtn.addEventListener('click', (e) => {
                this.selectAll();
                e.stopPropagation();
            });
            
            clearAllBtn.addEventListener('click', (e) => {
                this.clearAll();
                e.stopPropagation();
            });
            
            applyBtn.addEventListener('click', (e) => {
                this.closeDropdown();
                this.triggerChange();
                e.stopPropagation();
            });
        }
    }

    getLabelText() {
        // Try to find associated label
        const selectId = this.selectElement.id;
        if (selectId) {
            const label = document.querySelector(`label[for="${selectId}"]`);
            if (label) {
                return label.textContent;
            }
        }
        return '';
    }

    toggleDropdown() {
        if (this.dropdown.classList.contains('active')) {
            this.closeDropdown();
        } else {
            this.openDropdown();
        }
    }

    openDropdown() {
        this.dropdown.classList.add('active');
        this.header.classList.add('active');
        
        if (this.options.showSearch) {
            setTimeout(() => {
                this.searchInput.focus();
            }, 100);
        }
    }

    closeDropdown() {
        this.dropdown.classList.remove('active');
        this.header.classList.remove('active');
        
        if (this.options.showSearch) {
            this.searchInput.value = '';
            // Reset search results
            const options = this.optionsContainer.querySelectorAll('.multi-select-option');
            options.forEach(option => {
                option.style.display = '';
            });
        }
    }

    selectAll() {
        const options = Array.from(this.selectElement.options);
        options.forEach(option => {
            if (option.value) {
                this.selectedOptions.add(option.value);
            }
        });
        
        const checkboxes = this.optionsContainer.querySelectorAll('input[type="checkbox"]');
        checkboxes.forEach(checkbox => {
            checkbox.checked = true;
            checkbox.closest('.multi-select-option').classList.add('selected');
        });
        
        this.updateSelectElement();
        this.updatePlaceholder();
        
        if (this.options.showTags) {
            this.renderTags();
        }
    }

    clearAll() {
        this.selectedOptions.clear();
        
        const checkboxes = this.optionsContainer.querySelectorAll('input[type="checkbox"]');
        checkboxes.forEach(checkbox => {
            checkbox.checked = false;
            checkbox.closest('.multi-select-option').classList.remove('selected');
        });
        
        this.updateSelectElement();
        this.updatePlaceholder();
        
        if (this.options.showTags) {
            this.renderTags();
        }
    }

    updateSelectElement() {
        const options = Array.from(this.selectElement.options);
        options.forEach(option => {
            option.selected = this.selectedOptions.has(option.value);
        });
    }

    updatePlaceholder() {
        const placeholderSpan = this.header.querySelector('.multi-select-placeholder');
        const selectedCount = this.selectedOptions.size;
        
        if (selectedCount === 0) {
            placeholderSpan.textContent = this.options.placeholder;
            placeholderSpan.style.color = '#999';
        } else {
            const selectedText = `${selectedCount} ${this.options.selectedItemsText}`;
            placeholderSpan.innerHTML = selectedText + `<span class="multi-select-selected-count">${selectedCount}</span>`;
            placeholderSpan.style.color = '#2A2A2A';
        }
    }

    renderTags() {
        if (!this.options.showTags) return;
        
        this.tagsContainer.innerHTML = '';
        
        if (this.selectedOptions.size === 0) {
            return;
        }
        
        const options = Array.from(this.selectElement.options);
        const selectedOptions = options.filter(option => this.selectedOptions.has(option.value));
        const maxVisible = this.options.maxTagsVisible;
        
        const visibleOptions = selectedOptions.slice(0, maxVisible);
        
        visibleOptions.forEach(option => {
            const tag = document.createElement('div');
            tag.className = 'multi-select-tag';
            tag.textContent = option.textContent;
            
            const removeBtn = document.createElement('span');
            removeBtn.className = 'multi-select-tag-remove';
            removeBtn.textContent = '×';
            removeBtn.addEventListener('click', (e) => {
                this.selectedOptions.delete(option.value);
                
                // Update checkbox in dropdown
                const checkbox = this.optionsContainer.querySelector(`input[value="${option.value}"]`);
                if (checkbox) {
                    checkbox.checked = false;
                    checkbox.closest('.multi-select-option').classList.remove('selected');
                }
                
                this.updateSelectElement();
                this.updatePlaceholder();
                this.renderTags();
                
                e.stopPropagation();
            });
            
            tag.appendChild(removeBtn);
            this.tagsContainer.appendChild(tag);
        });
        
        if (selectedOptions.length > maxVisible) {
            const moreTag = document.createElement('div');
            moreTag.className = 'multi-select-tag';
            moreTag.textContent = `+ ${selectedOptions.length - maxVisible} ${this.options.selectedItemsText}`;
            this.tagsContainer.appendChild(moreTag);
        }
    }

    triggerChange() {
        const event = new Event('change', { bubbles: true });
        this.selectElement.dispatchEvent(event);
        
        // If we need to support IE11
        if (typeof(Event) === 'function') {
            // Modern browsers
            const event = new Event('change', { bubbles: true });
            this.selectElement.dispatchEvent(event);
        } else {
            // IE11
            const event = document.createEvent('Event');
            event.initEvent('change', true, true);
            this.selectElement.dispatchEvent(event);
        }
    }

    // Public methods
    getValue() {
        return Array.from(this.selectedOptions);
    }

    setValue(values) {
        this.selectedOptions.clear();
        values.forEach(value => {
            this.selectedOptions.add(value);
        });
        
        const checkboxes = this.optionsContainer.querySelectorAll('input[type="checkbox"]');
        checkboxes.forEach(checkbox => {
            const isSelected = values.includes(checkbox.value);
            checkbox.checked = isSelected;
            
            if (isSelected) {
                checkbox.closest('.multi-select-option').classList.add('selected');
            } else {
                checkbox.closest('.multi-select-option').classList.remove('selected');
            }
        });
        
        this.updateSelectElement();
        this.updatePlaceholder();
        
        if (this.options.showTags) {
            this.renderTags();
        }
    }

    reset() {
        this.clearAll();
    }
}

// Initialiser les multi-select lorsque le DOM est chargé
document.addEventListener('DOMContentLoaded', function() {
    // Transformer tous les selects avec la classe 'filter-multi-select' en multi-select modernes
    const filterSelects = document.querySelectorAll('.filter-multi-select');
    filterSelects.forEach((select, index) => {
        new ModernMultiSelect(`#${select.parentElement.id || `filter-container-${index}`}`, {
            placeholder: "Sélectionnez des filtres...",
            showTags: true,
            maxTagsVisible: 2
        });
    });

    // Transformer spécifiquement les sélecteurs de la carte
    const mapFilterSelects = document.querySelectorAll('.map-filter-select');
    mapFilterSelects.forEach((select, index) => {
        new ModernMultiSelect(`#${select.parentElement.id || `map-filter-${index}`}`, {
            placeholder: "Filtrer la carte...",
            showTags: true,
            showSearch: true
        });
    });
});

// Support pour les filtres AJAX
document.addEventListener('terralize_filters_loaded', function() {
    // Réinitialiser les multi-select après le chargement des filtres via AJAX
    const filterSelects = document.querySelectorAll('.filter-multi-select');
    filterSelects.forEach((select, index) => {
        new ModernMultiSelect(`#${select.parentElement.id || `filter-container-${index}`}`);
    });
}); 