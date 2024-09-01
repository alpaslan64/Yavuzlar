document.addEventListener('DOMContentLoaded', () => {
    const questionListElement = document.getElementById('question-list');
    const scoreDisplay = document.getElementById('score-display');
    let score = 0;
    let currentQuestionIndex = 0;
    let questions = [];

    if (questionListElement) {
        renderQuestions();
        document.getElementById('question-form').addEventListener('submit', (e) => {
            e.preventDefault();
            addQuestion();
        });
    }

    if (scoreDisplay) {
        questions = getQuestions();
        if (questions.length === 0) {
            alert("Soru bulunamadı!");
            location.href = 'index.html';
        } else {
            loadQuestion();
        }
    }

    function addQuestion() {
        const questionText = document.getElementById('question-text').value.trim();
        const options = [
            document.getElementById('option-a').value.trim(),
            document.getElementById('option-b').value.trim(),
            document.getElementById('option-c').value.trim(),
            document.getElementById('option-d').value.trim()
        ];
        const correctOption = document.getElementById('correct-option').value.trim().toUpperCase();

        if (!questionText || options.some(option => !option) || !['A', 'B', 'C', 'D'].includes(correctOption)) {
            alert("Lütfen tüm alanları doğru şekilde doldurduğunuzdan emin olun.");
            return;
        }

        questions = getQuestions();
        questions.push({ text: questionText, options: options, correct: correctOption });
        localStorage.setItem('questions', JSON.stringify(questions));

        renderQuestions();
        resetForm();
    }

    function getQuestions() {
        return JSON.parse(localStorage.getItem('questions')) || [];
    }

    function renderQuestions() {
        const questions = getQuestions();
        questionListElement.innerHTML = '';

        questions.forEach((question, index) => {
            const li = document.createElement('li');
            li.textContent = `${question.text} (Doğru Şık: ${question.correct})`;
            const editBtn = document.createElement('button');
            editBtn.textContent = 'Düzenle';
            editBtn.addEventListener('click', () => editQuestion(index));
            const deleteBtn = document.createElement('button');
            deleteBtn.textContent = 'Sil';
            deleteBtn.addEventListener('click', () => deleteQuestion(index));
            li.appendChild(editBtn);
            li.appendChild(deleteBtn);
            questionListElement.appendChild(li);
        });
    }

    function editQuestion(index) {
        questions = getQuestions();
        const question = questions[index];
        document.getElementById('question-text').value = question.text;
        document.getElementById('option-a').value = question.options[0];
        document.getElementById('option-b').value = question.options[1];
        document.getElementById('option-c').value = question.options[2];
        document.getElementById('option-d').value = question.options[3];
        document.getElementById('correct-option').value = question.correct;

        questions.splice(index, 1);
        localStorage.setItem('questions', JSON.stringify(questions));
        renderQuestions();
    }

    function deleteQuestion(index) {
        questions = getQuestions();
        questions.splice(index, 1);
        localStorage.setItem('questions', JSON.stringify(questions));
        renderQuestions();
    }

    function resetForm() {
        document.getElementById('question-form').reset();
    }

    function loadQuestion() {
        if (currentQuestionIndex >= questions.length) {
            setTimeout(() => {
                alert("Quiz tamamlandı!");
                location.href = 'index.html';
            }, 500);
            return;
        }

        const question = questions[currentQuestionIndex];

        document.getElementById('question-text-display').textContent = question.text;
        const optionsList = document.getElementById('options-list');
        optionsList.innerHTML = '';

        question.options.forEach((option, index) => {
            const li = document.createElement('li');
            li.textContent = option;
            li.addEventListener('click', () => {
                checkAnswer(index);
            });
            optionsList.appendChild(li);
        });
    }

    function checkAnswer(selectedOptionIndex) {
        const question = questions[currentQuestionIndex];
        const correctIndex = ['A', 'B', 'C', 'D'].indexOf(question.correct);
        if (selectedOptionIndex === correctIndex) {
            score++;
            scoreDisplay.textContent = `Puan: ${score}`;
        }
        currentQuestionIndex++;
        loadQuestion();
    }
});
