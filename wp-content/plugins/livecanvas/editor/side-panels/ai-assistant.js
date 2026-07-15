// AI General Prompter js

// calculate price of tokens
function calculatePriceOfTokens(tokens, model, input = true) {
    const modelPrice = {
        // price per million tokens
        "gpt-4-turbo": {
            'input': 10,
            'output': 30,
        },
        "gpt-4": {
            'input': 30,
            'output': 60,
        },
        "gpt-4o": {
            'input': 5,
            'output': 15,
        },
        "gpt-4o-mini": {
            'input': 0.15,
            'output': 0.60,
        },
    }

    const price = tokens / 1000000 * modelPrice[model][input ? 'input' : 'output'];
    return price;

}

function getAiServiceFromOptgroupLabel(label) {
    if (/openrouter/i.test(label || "")) {
        return "OpenRouter";
    }
    if (/openai/i.test(label || "")) {
        return "OpenAI";
    }
    return "";
}

function hasConfiguredApiKey(value) {
    if (typeof value === "string") {
        return value.trim() !== "";
    }
    return !!value;
}

function getCurrentAiServiceKeyFromSelect($select) {
    const $selectedOption = $select.find("option:selected").first();
    const $selectedOptgroup = $selectedOption.parent("optgroup");
    const explicitService = (($selectedOptgroup.attr("data-service") || "") + "").toLowerCase();

    if (explicitService === "openai" || explicitService === "openrouter") {
        return explicitService;
    }

    const serviceFromLabel = getAiServiceFromOptgroupLabel($selectedOptgroup.attr("label") || "");
    if (serviceFromLabel === "OpenAI") {
        return "openai";
    }
    if (serviceFromLabel === "OpenRouter") {
        return "openrouter";
    }

    return "";
}

function updateModelSelectServiceBadge($select) {
    const serviceKey = getCurrentAiServiceKeyFromSelect($select);
    if (serviceKey) {
        $select.attr("data-current-service", serviceKey);
    } else {
        $select.removeAttr("data-current-service");
    }
}

function toggleMainAiModelOptgroupsByApiKeys() {
    const hasOpenAI = hasConfiguredApiKey(window.lc_editor_openai_key);
    const hasOpenRouter = hasConfiguredApiKey(window.lc_editor_openrouter_key);

    const modelSelectNames = [
        "ai_assistant__choose_model",
        "ai_screenshot__choose_model",
    ];

    modelSelectNames.forEach((selectName) => {
        const $select = $(`select[name=${selectName}]`);
        if (!$select.length) {
            return;
        }

        const $openAIOptgroups = $select.find("optgroup").filter(function () {
            return /openai/i.test($(this).attr("label") || "");
        });
        const $openRouterOptgroups = $select.find("optgroup").filter(function () {
            return /openrouter/i.test($(this).attr("label") || "");
        });

        $openAIOptgroups.prop("hidden", !hasOpenAI).css("display", hasOpenAI ? "" : "none");
        $openRouterOptgroups.prop("hidden", !hasOpenRouter).css("display", hasOpenRouter ? "" : "none");

        const $placeholder = $select.find('option[data-no-provider-placeholder="1"]');
        const $visibleEnabledOptions = $select.find("optgroup").filter(function () {
            return !this.hidden;
        }).find("option").filter(function () {
            return !this.disabled;
        });
        const hasAnyVisibleOption = $visibleEnabledOptions.length > 0;

        if (!hasAnyVisibleOption) {
            if (!$placeholder.length) {
                $select
                    .append(
                        $("<option></option>")
                            .attr("value", "")
                            .attr("data-no-provider-placeholder", "1")
                            .prop("selected", true)
                            .prop("disabled", true)
                            .text("No AI provider available (set API key in settings)")
                    );
            }
            $select.val("");
            updateModelSelectServiceBadge($select);
            return;
        }

        if ($placeholder.length) {
            $placeholder.remove();
        }

        const $selectedOption = $select.find("option:selected").first();
        const $selectedOptgroup = $selectedOption.parent("optgroup");
        const selectedInVisibleOptgroup = $selectedOption.length &&
            $selectedOptgroup.length &&
            !$selectedOptgroup.prop("hidden");

        if (!selectedInVisibleOptgroup) {
            const fallbackValue = $visibleEnabledOptions.first().val();

            if (fallbackValue) {
                $select.val(fallbackValue);
                if (selectName === "ai_assistant__choose_model") {
                    $select.data("last-valid-model", fallbackValue);
                }
            }
        }

        updateModelSelectServiceBadge($select);
    });
}

function getAssistantModelsSelect() {
    return $('select[name=ai_assistant__choose_model]');
}

function getAssistantModelOptgroup(serviceKey) {
    const $select = getAssistantModelsSelect();
    const $target = $select.find(`optgroup[data-all-models-target="${serviceKey}"]`).first();
    if ($target.length) {
        return $target;
    }

    const regex = serviceKey === "openai" ? /openai/i : /openrouter/i;
    return $select
        .find("optgroup")
        .filter(function () {
            return regex.test($(this).attr("label") || "");
        })
        .first();
}

function hasModelOption($select, modelValue) {
    return $select.find("option").filter(function () {
        return $(this).val() === modelValue;
    }).length > 0;
}

function getFallbackModel($select) {
    const firstUsableOption = $select.find("option").filter(function () {
        const value = $(this).val();
        return value &&
            value !== "load-all-openai-models" &&
            value !== "load-all-openrouter-models" &&
            !this.disabled;
    }).first();

    return firstUsableOption.length ? firstUsableOption.val() : "";
}

async function fetchAllOpenAIModels() {
    if (!lc_editor_openai_key) {
        throw new Error("OpenAI API key missing. Add it in LiveCanvas settings first.");
    }

    const response = await fetch("https://api.openai.com/v1/models", {
        method: "GET",
        headers: {
            Authorization: `Bearer ${lc_editor_openai_key}`,
        },
    });

    if (!response.ok) {
        throw new Error(`Unable to load OpenAI models (HTTP ${response.status})`);
    }

    const data = await response.json();
    const models = Array.isArray(data.data) ? data.data : [];
    return models
        .map((model) => ({
            value: model.id,
            label: model.id,
        }))
        .filter((model) => !!model.value)
        .sort((a, b) => a.value.localeCompare(b.value));
}

async function fetchAllOpenRouterModels() {
    const headers = {};
    if (lc_editor_openrouter_key) {
        headers.Authorization = `Bearer ${lc_editor_openrouter_key}`;
    }

    const response = await fetch("https://openrouter.ai/api/v1/models", {
        method: "GET",
        headers,
    });

    if (!response.ok) {
        throw new Error(`Unable to load OpenRouter models (HTTP ${response.status})`);
    }

    const data = await response.json();
    const models = Array.isArray(data.data) ? data.data : [];
    return models
        .map((model) => {
            const modelId = model.id || "";
            const modelName = model.name || modelId;
            return {
                value: modelId,
                label: modelName === modelId ? modelId : `${modelName} (${modelId})`,
            };
        })
        .filter((model) => !!model.value)
        .sort((a, b) => a.value.localeCompare(b.value));
}

async function loadAndAppendModelsInOptgroup(serviceKey) {
    const serviceDisplayName = serviceKey === "openai" ? "OpenAI" : "OpenRouter";
    const allModelsLabel = serviceKey === "openai" ? "All OpenAI models" : "All OpenRouter models";
    const fetcher = serviceKey === "openai" ? fetchAllOpenAIModels : fetchAllOpenRouterModels;
    const serviceRegex = serviceKey === "openai" ? /openai/i : /openrouter/i;

    const $select = getAssistantModelsSelect();
    const $sourceOptgroup = getAssistantModelOptgroup(serviceKey);
    if (!$sourceOptgroup.length) {
        throw new Error(`Unable to find ${serviceDisplayName} optgroup in model selector`);
    }

    const fetchedModels = await fetcher();
    const $serviceOptgroups = $select.find("optgroup").filter(function () {
        return serviceRegex.test($(this).attr("label") || "");
    });
    $serviceOptgroups.not($sourceOptgroup).remove();

    // Replace the original provider optgroup with the full models list.
    $sourceOptgroup
        .attr("label", allModelsLabel)
        .attr("data-all-models-for", serviceKey)
        .empty();

    fetchedModels.forEach((model) => {
        const $option = $("<option></option>")
            .attr("value", model.value)
            .text(model.label);
        $sourceOptgroup.append($option);
    });

    if (fetchedModels.length) {
        $select.val(fetchedModels[0].value).data("last-valid-model", fetchedModels[0].value);
    } else {
        const fallback = getFallbackModel($select);
        if (fallback) {
            $select.val(fallback).data("last-valid-model", fallback);
        }
    }

    swal(`${serviceDisplayName} models loaded in "${allModelsLabel}": ${fetchedModels.length} models.`);
}

// AI ASSISTANT: load all models in the selected provider optgroup ////////////////////////
$("body").on("change", "select[name=ai_assistant__choose_model]", async function () {
    const $select = $(this);
    const selectedValue = $select.val();
    updateModelSelectServiceBadge($select);

    if (selectedValue !== "load-all-openai-models" && selectedValue !== "load-all-openrouter-models") {
        $select.data("last-valid-model", selectedValue);
        if (selectedValue) {
            setEditorPreference("ai_assistant_selected_model", selectedValue);
        }
        return;
    }

    const serviceKey = selectedValue === "load-all-openai-models" ? "openai" : "openrouter";
    const previousModel = $select.data("last-valid-model");

    $select.prop("disabled", true);
    try {
        await loadAndAppendModelsInOptgroup(serviceKey);
        updateModelSelectServiceBadge($select);
        const finalSelectedModel = $select.val();
        if (finalSelectedModel) {
            setEditorPreference("ai_assistant_selected_model", finalSelectedModel);
        }
    } catch (error) {
        if (previousModel && hasModelOption($select, previousModel)) {
            $select.val(previousModel);
        } else {
            $select.val(getFallbackModel($select));
        }
        const restoredModel = $select.val();
        updateModelSelectServiceBadge($select);
        if (restoredModel) {
            setEditorPreference("ai_assistant_selected_model", restoredModel);
        }
        swal(error.message || "Unable to load models.");
    } finally {
        $select.prop("disabled", false);
    }
});

$("body").on("change", "select[name=ai_screenshot__choose_model]", function () {
    updateModelSelectServiceBadge($(this));
    const selectedModel = $(this).val();
    if (selectedModel) {
        setEditorPreference("ai_screenshot_selected_model", selectedModel);
    }
});

$(function () {
    toggleMainAiModelOptgroupsByApiKeys();
    $('select[name=ai_assistant__choose_model], select[name=ai_screenshot__choose_model]').each(function () {
        updateModelSelectServiceBadge($(this));
    });
});

// AI ASSISTANT: CHOOSE PROMPT //////////////////////////////////////////////
// When user selects a prompt from the SELECT, the selected option value is copied to textarea
$("body").on("change", "select[name=ai_assistant_choose_prompt]", async function (e) {
    e.preventDefault();
    const chosenPrompt = $(this).val();
    $("textarea[name='ai-user-prompt']").val(chosenPrompt);
});

// AI ASSISTANT GENERAL SUBMIT //////////////////////////////////////////////
// user clicks Submit Button in general AI assistant: make the request
$("body").on("click", "input[name=ai-assistant-submit]", async function (e) {
    e.preventDefault();

    const selectedModel = $('select[name=ai_assistant__choose_model]').val();
    setEditorPreference("ai_assistant_selected_model", selectedModel);

    const selectedOptgroupLabel = $('select[name=ai_assistant__choose_model] option:selected').parent('optgroup').attr('label');
    const ai_service = getAiServiceFromOptgroupLabel(selectedOptgroupLabel);

    let endpoint = "";
    let ai_apikey = "";

    if (ai_service=="") {
        alert("No AI provider selected");
        return;
    }
    if (ai_service =='OpenAI') {
        endpoint = "https://api.openai.com/v1/chat/completions";
        ai_apikey = lc_editor_openai_key;
    }
    if (ai_service == 'OpenRouter') {
        endpoint = 'https://openrouter.ai/api/v1/chat/completions';
        ai_apikey = lc_editor_openrouter_key;
    }

    //if no key provided, help the user
    if (ai_apikey == "") {
        swal({
            title: "No " + ai_service + " API Key",
            text: "Would you like to set the API key in the backend panel?",
            icon: "warning",
            buttons: true,
        })
            .then((willGoToSettings) => {
                if (willGoToSettings) {
                    // If the user confirms, open the settings page in a new tab
                    window.open(lc_editor_saving_url.replace('admin-ajax.php', 'admin.php?page=livecanvas_integration'), '_blank').focus();
                }
            });
        return false;
    }

    // disable the button
    $(this).prop("disabled", true);
    const selector = $(this).closest("[selector]").attr("selector");
    // get the page_id param from url
    const page_id = new URLSearchParams(window.location.search).get("page_id");
    const currentHtml = getPageHTMLOuter(selector);
    let pageTextContent = previewFrame
        .contents()
        .find('main') // Target the main element
        .clone() // Clone the main element to work with it safely
        .find('script, style, noscript') // Exclude unwanted tags
        .remove() // Remove them from the clone
        .end() // Return to the cloned element
        .text() // Extract the textual content
        .trim() // Remove extra spaces at the start and end
        .replace(/\s+/g, ' '); // Normalize whitespace to a single space

    //const pageTextContent = doc.querySelector('main').innerText.trim().replace(/\s+/g, ' ');  //old alternative
    //myConsoleLog(pageTextContent);
    const userPrompt = document.querySelector("textarea[name=ai-user-prompt]").value;
    const includeWebsiteInfo = $("input[name=ai-context-website-info]").prop('checked');
    const includePageContent = $("input[name=ai-context-page-text]").prop('checked');


    const createPrompt = function () {
        let prompt = `To complete your task you are given this HTML code that you must edit.
            The code is using ${theFramework.name} ${theFramework.version} and you must respect it.
            When adding new HTML code, all text regions, if they have a wrapping DIV element, that DIV should have attribute editable="rich".

            If needed, add JS code via a single inline <script> tag. If you do, add this comment inside the script tag:
            //lc-needs-hard-refresh
            The Script shall be inside the first HTML element.

            If you encounter Lorem Ipsum placeholders, you must always change them with real text.

            Here is the code to edit:

            <HTML-code-to-edit>
            ${currentHtml}
            </HTML-code-to-edit>`;


        if (includeWebsiteInfo) {
            prompt += `
                Here is some business background information to use when necessary to execute the task:
                <business-background-information>
                ${lc_editor_website_info}
                </business-background-information>
                If business background information is empty, just complete the task.
            `;
        }

        if (includePageContent) {
            prompt += `
                Moreover, here is the textual content of the whole page.
                Use it to perform the task coherently.

                <page-content>
                ${pageTextContent}
                </page-content>

            `;
        }

        prompt += `

            Here is the task:
            ${userPrompt}
        `;

        return prompt;
    }

    const thePrompt = createPrompt();

    //myConsoleLog("Prompt: \n" + thePrompt);

    const query = {
        model: selectedModel,
        messages: [{
            role: "system",
            content: `You are a web developer. When making HTML you will always prefer to use ${theFramework.name} ${theFramework.version} or NinjaBootstrap classes over plain css.

            Please provide ONLY the HTML code (without including anything else nor embedding the html into a markdown block) for the following task.

            If you are asked to only change text NEVER alter the HTML structure nor the classes. If you are asked to change the structure or classes, you can change them as needed.`,
        },
        {
            role: "user",
            content: thePrompt,
        },
        ],
    };

    if (0) { ////FOR DEBUG
        console.log("AI query:");
        console.log(query);
    }

    $("html").addClass("system-is-working");

    fetch(endpoint, {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            Authorization: `Bearer ${ai_apikey}`,
        },
        body: JSON.stringify(query),
    })
        .then(function (response) {
            if (!response.ok) {
                let theErrorMessage = 'Response not ok. Status: ' + response.status;

                if (response.status == 401) {
                    theErrorMessage = "Error from " + ai_service + ": 'Forbidden'. API key may be incorrect.";
                }
                swal(theErrorMessage);
                throw new Error(theErrorMessage);
            }
            return response.json();
        })
        .then(async (data) => {
            $("html").removeClass("system-is-working");
            //console.log(data); // Logs the full response object to the console

            // enable the buttons
            $(this).prop("disabled", false);
            $('[name="ai-assistant-reject"]').prop("disabled", false);

            //check if we got answers from AI
            if (data.choices && data.choices.length > 0) {

                let responseText = data.choices[0].message.content; // Assuming the structure matches the response

                const selector = $(this).closest("[selector]").attr("selector");

                //for G gemini, strip delimiters
                if (responseText.startsWith("```html") && responseText.endsWith("```")) {
                    responseText = responseText.slice(7, -3);
                }
                setPageHTMLOuter(selector, responseText);
                updatePreviewSectorial(selector);

            }

            // calculate the price:
            const tokens = data.usage.prompt_tokens;
            const outputTokens = data.usage.completion_tokens;
            const model = selectedModel;

            await LiveCanvasAIHistoryDBService.saveData({
                datetime: new Date().toLocaleTimeString(),
                date: new Date().toISOString().split('T')[0],
                model: selectedModel,
                tokens: tokens,
                outputTokens: outputTokens,
                prompt: userPrompt
            });

            const priceInput = calculatePriceOfTokens(tokens, model, true);
            const priceOutput = calculatePriceOfTokens(tokens, model, false);
            myConsoleLog(`Approximate Price for input tokens: ${priceInput} and for output tokens: ${priceOutput} for a total of ${priceInput + priceOutput}`);
        })
        .catch((error) => {
            $("#ai-assistant-error").innerText = error;
            $(this).prop("disabled", false);
            $("html").removeClass("system-is-working");
        });

    // Make sure you have an HTML element with the ID 'chat-output' in your HTML.
}); //end function

// user clicks REJECT button
$("body").on("click", "input[name=ai-assistant-reject]", async function (e) {
    e.preventDefault();
    const selector = $(this).closest("[selector]").attr("selector");
    //console.log(selector);
    const oldHtml = localStorage.getItem('backup_outerhtml_before_ai')
    setPageHTMLOuter(selector, oldHtml);
    updatePreviewSectorial(selector);
    // enable the button
    $(this).prop("disabled", false);
});



//PROMPT HISTORY
class PromptHistoryAiWindow {
    constructor() {
        if (!PromptHistoryAiWindow.instance) {
            this.winBox = null;
            this.loading = false;
            this.loaded = false;
            this.data = [];
            this.subscriptionId = null;
            PromptHistoryAiWindow.instance = this;
        }
        return PromptHistoryAiWindow.instance;
    }


    open() {
        if (this.winBox) {
            return; // Window is already open
        }
        this.winBox = new WinBox({
            id: "prompt-history-window",
            title: "Prompt History",
            class: ["no-full", "no-max", "my-theme"],
            html: `<div id="prompt-history-container">
                <div id="prompt-history-filter" style="padding: 10px; background-color: rgba(0,0,0,0.2);">
                    <input type="text"
                        id="prompt-filter-input" placeholder="Filter prompts..." style="width: 100%; padding: 8px; border-radius: 4px; border: 1px solid rgba(255,255,255,0.3); background-color: rgba(0,0,0,0.2); color: white;">
                </div>
                <div id="prompt-history-body"></div>
                <script>
                    $('#prompt-filter-input').on('input keyup', function() {
                        const filterValue = $(this).val();
                        PromptHistoryAiWindow.instance.filterPrompts(filterValue);
                    });
                </script>
            </div>`,
            background: "#e83e8c",
            border: 4,
            width: 550,
            height: "50%",
            minheight: 55,
            minwidth: 100,
            x: "center",
            top: 45,
            right: 0,
            onclose: () => {
                $(".open-prompt-history").removeClass("is-active");
                this.unsubscribeFromDataChanges();
                this.winBox = null;
            }
        });

        // Add event listener for filter input
        setTimeout(() => {
            const filterInput = document.getElementById('prompt-filter-input');
            if (filterInput) {
                filterInput.addEventListener('input', this.filterPrompts.bind(this));
            }
        }, 100); // Small delay to ensure DOM is ready

        this.loading = true;
        // query the data
        this.loadData();
        this.render();
        this.loading = false;

        // Subscribe to database changes
        this.subscribeToDataChanges();
    }


    async loadData() {
        try {
            const data = await LiveCanvasAIHistoryDBService.getAllData();
            this.data = data.sort((a, b) => b.id - a.id);
            this.render();
        } catch (error) {
            this.data = [];
            console.error("Error loading data:", error);
        }
    }

    filterPrompts() {
        const filterValue = document.getElementById('prompt-filter-input').value.toLowerCase();
        this.render(filterValue);
    }

    render(filterValue = '') {
        if (!this.winBox) {
            return; // Window is not open
        }
        const body = document.querySelector("#prompt-history-body");
        body.innerHTML = "";
        if (this.loading) {
            body.innerHTML = `<div class="loading">Loading...</div>`;
            return;
        }
        if (this.data.length === 0) {
            body.innerHTML = `<div class="no-data">No data</div>`;
            return;
        }

        // Filter data if filterValue is provided
        const filteredData = filterValue ?
            this.data.filter(item => item.prompt.toLowerCase().includes(filterValue)) :
            this.data;


        const table = document.createElement("table");
        table.style.width = "100%";
        table.style.borderCollapse = "collapse";
        table.style.marginBottom = "15px";
        table.style.color = "#fff";
        table.style.fontSize = "14px";
        table.innerHTML = `
            <tr>
                <th style="padding: 10px; text-align: left; border-bottom: 2px solid #e83e8c; width: 30%;">Date</th>
                <th style="padding: 10px; text-align: left; border-bottom: 2px solid #e83e8c; width: 70%;">Prompt</th>
                <th style="padding: 10px; text-align: left; border-bottom: 2px solid #e83e8c; width: 80px;"></th>
            </tr>
        `;
        filteredData.forEach((item) => {
            const row = document.createElement("tr");
            row.style.transition = "background-color 0.2s";
            row.onmouseover = function() { this.style.backgroundColor = 'rgba(232, 62, 140, 0.2)'; };
            row.onmouseout = function() { this.style.backgroundColor = 'transparent'; };

            const tdOne = document.createElement("td");
            tdOne.style.padding = "8px 10px";
            tdOne.style.borderBottom = "1px solid rgba(255,255,255,0.1)";
            tdOne.innerText = `${item.date} ${item.datetime}`;
            row.appendChild(tdOne);
            const tdTwo = document.createElement("td");
            tdTwo.style.padding = "8px 10px";
            tdTwo.style.borderBottom = "1px solid rgba(255,255,255,0.1)";
            tdTwo.id = `history-prompt-td-row-${item.id}`;
            tdTwo.innerText = item.prompt;
            row.appendChild(tdTwo);
            const tdThree = document.createElement("td");
            tdThree.style.padding = "8px 10px";
            tdThree.style.borderBottom = "1px solid rgba(255,255,255,0.1)";
            tdThree.style.width = "80px";
            const useAgainButton = document.createElement("button");
            useAgainButton.className = "use-again-btn";
            useAgainButton.style.backgroundColor = "#17a2b8";
            useAgainButton.style.color = "white";
            useAgainButton.style.border = "none";
            useAgainButton.style.borderRadius = "3px";
            useAgainButton.style.padding = "3px 8px";
            useAgainButton.style.fontSize = "12px";
            useAgainButton.style.cursor = "pointer";
            useAgainButton.setAttribute('data-prompt', item.prompt.replace(/"/g, '&quot;'));
            useAgainButton.innerText = "Use again";
            tdThree.appendChild(useAgainButton);
            row.appendChild(tdThree);

            table.appendChild(row);
        })
        body.appendChild(table);

        // Add event listener for 'Use again' buttons
        document.querySelectorAll('.use-again-btn').forEach(button => {
            button.addEventListener('click', function() {
                const promptText = this.getAttribute('data-prompt');
                $('textarea[name="ai-user-prompt"]').val(promptText);
                // bring focus to the prompt textarea
                $('textarea[name="ai-user-prompt"]').focus();
                // Optional: Close the prompt history window after selecting a prompt
                // PromptHistoryAiWindow.instance.close();
            });
        });
    }

    /**
     * Subscribe to database changes to update the UI in real-time
     */
    subscribeToDataChanges() {
        // Unsubscribe first to avoid duplicate subscriptions
        this.unsubscribeFromDataChanges();

        // Subscribe to data changes
        this.subscriptionId = LiveCanvasAIHistoryDBService.subscribe((action, data) => {
            if (action === 'add') {
                // Add the new data to the beginning of the array
                this.data.unshift(data);
                // Re-render the UI with the current filter
                const filterInput = document.getElementById('prompt-filter-input');
                const filterValue = filterInput ? filterInput.value.toLowerCase() : '';
                this.render(filterValue);
            }
        });
    }

    /**
     * Unsubscribe from database changes
     */
    unsubscribeFromDataChanges() {
        if (this.subscriptionId) {
            LiveCanvasAIHistoryDBService.unsubscribe(this.subscriptionId);
            this.subscriptionId = null;
        }
    }

    close() {
        if (this.winBox) {
            this.unsubscribeFromDataChanges();
            this.winBox.close();
            this.winBox = null;
        }
    }
}






//user clicks link to open prompt history
$("#sidepanel").on("click", ".open-prompt-history", function (event) {
    event.preventDefault();
    const promptHistoryViewWindow = new PromptHistoryAiWindow();
    $(this).toggleClass("is-active");

    if ($(this).hasClass("is-active")) {

        promptHistoryViewWindow.open();

        //open the tree. Opinioned expanding
        $("#tree-expand-all").click();
    } else {
        promptHistoryViewWindow.close();
    }
});
