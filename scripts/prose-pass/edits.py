"""First-person prose pass for the Thai Solitude site (localhost:8090 rehearsal).

Each edit is (old, new). `old` must occur exactly once in its target, or apply.py
refuses the whole target. Research sentences (citation-locked), the approved
health note, and the approved severe-pain line are deliberately not touched.

Voice: Butsaba, first person "I" (Decisions Register 2.x, Ryan 2026-10-04).
No "we/our/us" (OBMT term list), no "studio", no outcome promises.
"""

BANNER = [
    ("Thai Solitude plans to begin massage sessions in November. Questions are welcome now—<a href=\"/contact/\">get in touch with Butsaba</a>.",
     "I plan to begin massage sessions in November. Questions are welcome now: <a href=\"/contact/\">get in touch with me</a>."),
]

PAGES = {
    # ---------------------------------------------------------------- Home
    296: BANNER + [
        ("Personalized Thai and therapeutic massage, shaped around your goals and comfort.",
         "Thai and therapeutic massage that starts with what you tell me and changes as you give feedback."),
        ("Discover a thoughtful approach to massage therapy.",
         "Every session starts with your goals."),
        ("Founded by Butsaba Dusrai, an Oregon Licensed Massage Therapist, Thai Solitude brings Thai bodywork and therapeutic massage together. Butsaba listens to your goals and adapts each session to your comfort.",
         "I’m Butsaba Dusrai, an Oregon Licensed Massage Therapist, and Thai Solitude is my practice. I trained in traditional Thai bodywork and in massage therapy, and I draw on both. Before I begin, I ask what you want from the session, and I keep adjusting as you tell me how it feels."),
        ("Butsaba’s Therapeutic Massage Services",
         "The Massage I Offer"),
        ("Gentle to moderate pressure for clients seeking a quieter session and time to relax.",
         "Gentle to moderate pressure, for when you want a quieter session and time to relax."),
        ("Focused pressure for areas of persistent tightness, adapted to your comfort and feedback.",
         "Firm, focused pressure on areas that stay tight, and only as deep as you are comfortable with."),
        ("Traditional Thai bodywork combines gentle rocking, rhythmic compression, and assisted stretching to ease stiffness and support comfortable movement.",
         "My specialty. Rhythmic compression, gentle rocking, and assisted stretching, done fully clothed. I guide each stretch and never force it."),
        ("Why Choose Butsaba", "How I Work"),
        ("Professional Care", "Experience"),
        ("Butsaba brings massage experience since 2020 and tailors each session to your goals and preferences.",
         "I have practiced massage since 2020, and I plan each session around what you need that day."),
        ("Tell Butsaba what brings you in. She will listen, explain the approach, and welcome feedback throughout the session.",
         "Tell me what brings you in. I’ll explain what I plan to do before I start, and you can ask me to change it at any point."),
        ("Holistic Approach", "Thai and Therapeutic Training"),
        ("Thai bodywork and therapeutic techniques offer different ways to address your goals, from guided movement to a calmer massage session.",
         "Because I trained in both, a session can center on guided stretching and movement, on a quieter massage, or on some of each."),
    ],
    # ---------------------------------------------------------------- About
    298: BANNER + [
        ("Discover the Heart of Thai Solitude", "The Person Behind Thai Solitude"),
        ("Butsaba Dusrai is an Oregon Licensed Massage Therapist with hands-on experience since 2020.",
         "I’m an Oregon Licensed Massage Therapist, and I have practiced massage since 2020."),
        ("I trained in massage therapy at Estelle Medical Academy and in traditional Thai bodywork at Still Light Center. My work includes Thai, therapeutic, and relaxation massage. I begin by listening to your goals, then adapt the pressure, pace, and focus to your comfort.",
         "I trained in traditional Thai bodywork at Still Light Center and in massage therapy at Estelle Medical Academy. Thai massage is my specialty, and I also offer therapeutic and relaxation massage."),
        ("Thai Solitude is my independent practice, shaped by traditional Thai bodywork and a personal approach to therapeutic massage. Each session starts with a conversation and stays responsive to how you feel.",
         "Thai Solitude is my own practice. Each session starts with a conversation about what you hope to feel, where you are holding tension, and anything about your health I should know. While I work, I keep checking in, and you can change the plan at any time."),
        ("\"question\":\"My Commitment to Your Wellness\",\"answer\":\"My aim is to create a calm, professional space where you can share your goals and feel comfortable speaking up. I tailor the pace, pressure, and focus of each session to you.\"",
         "\"question\":\"Please Speak Up\",\"answer\":\"Many people stay quiet during a massage even when the pressure is too much. Please tell me. I would rather change the work than have you sit through something uncomfortable.\""),
        ("\"question\":\"Massage as Part of Everyday Care\",\"answer\":\"I want massage to feel approachable, whether you come in with a specific area of tension or simply need time to slow down.\"",
         "\"question\":\"No Reason Needed\",\"answer\":\"You don’t need a specific complaint to book. Some people come in with a stiff neck or a tight back; others simply need time to slow down. Both are good reasons to come.\""),
        ("\"question\":\"Values That Guide Every Session\",\"answer\":\"Trust, professionalism, and empathy shape my work. I listen to your goals, check in about comfort, and make room for your feedback throughout the session.\"",
         "\"question\":\"How I Check In\",\"answer\":\"Before I start, I ask about your goals and your health. During the session I check on pressure and comfort. Afterward, I ask how the work felt.\""),
        ("The Journey of Thai Solitude", "From Chicago to Portland"),
        ("I founded Thai Solitude in 2024 after beginning my massage work in 2020. My experience includes Thai bodywork and work in a busy massage setting. I look forward to welcoming Portland clients when sessions begin in November.",
         "I began working in massage in 2020 and started Thai Solitude in Chicago in 2024. I have also worked in a busy Chicago massage practice, where my clients included hotel guests and business travelers. Now I am bringing Thai Solitude to Portland, and I look forward to welcoming clients here when sessions begin in November."),
    ],
    # ---------------------------------------------------------------- Services
    299: BANNER + [
        ("Explore Butsaba’s Professional Massage Services", "My Massage Services"),
        ("Therapeutic massage uses focused pressure and a pace adapted to your comfort. Tell Butsaba where you feel tension and what you would like from the session; she can adjust the work as you go.",
         "Therapeutic massage gives steady attention to the places where you feel tension, at a pace and pressure that suit you. Tell me where it bothers you and what you would like from the session, and I will adjust as the session goes on."),
        ("Deep tissue massage uses focused, deliberate pressure for areas of persistent tightness. Butsaba checks in about pressure and adapts the work to your comfort.",
         "Deep tissue massage uses focused strokes and sustained pressure on areas that stay tight. Deep does not have to mean painful: I check in about pressure throughout, and you decide how firm it gets."),
        ("Traditional Thai massage blends gentle rocking, rhythmic compression, and assisted stretching to help ease stiffness and encourage comfortable movement. Butsaba adapts the pressure, pace, and range of each stretch to your needs, creating a session that can feel restful or more active. Ideal when you want bodywork that combines relaxation with guided movement.",
         "Thai massage is my specialty. It combines rhythmic compression, gentle rocking, and assisted stretching, and you stay fully clothed. I set the pressure, pace, and range of each stretch by how your body responds, so a session can be restful, more active, or somewhere in between."),
        (">Specialized<", ">Your Session<"),
        ("Your session begins with a conversation about what you would like from your time with Butsaba—focused work, deep relaxation, or a little of both. Together, you choose the pace, pressure, and techniques that feel right for you. The result is thoughtful, personal bodywork shaped around your comfort and goals.",
         "Every session begins with a conversation about what you want: focused work, deep relaxation, or some of each. You choose the pace and pressure, I suggest techniques that fit, and you or I can change course once the session starts."),
        ("Thai Solitude begins with a thorough consultation to understand your needs, preferences, and any specific concerns regarding discomfort, tension, or restricted movement.",
         "I start by asking what brings you in, where you feel tension or stiffness, and how much pressure you like."),
        ("Customized Treatment Plan", "Planning the Session"),
        ("Butsaba uses your goals and preferences to choose a comfortable pace, pressure, and focus for the session.",
         "From what you tell me, I choose a pace, pressure, and focus for the session and explain them before I begin."),
        ("Execution of Massage Techniques", "The Massage"),
        ("Butsaba applies specific massage techniques according to your treatment plan, focusing on areas that require the most attention and care, including muscle knots.",
         "I spend the most time where you asked for attention, including any knots, and check in about pressure as I go."),
        ("Post-Treatment Care and Follow-Up", "After the Session"),
        ("After your session, Butsaba can discuss how the work felt and whether a follow-up session would be useful.",
         "Afterward, I ask how the work felt. If you are thinking about another session, I am happy to talk it through with you."),
    ],
    # ---------------------------------------------------------------- Therapeutic
    1107: BANNER + [
        ("Therapeutic massage combines focused work with time to relax. Butsaba listens to what brings you in, then adapts pressure, pace, and areas of focus to your comfort. You can ask for attention to a particular area or choose a broader session; the plan can change as you give feedback.",
         "Therapeutic massage combines focused work with time to relax. I start with what brought you in. You can ask me to stay with one area, such as your neck and shoulders, or to work more broadly, and the plan can change as you give me feedback."),
        ("Clients may choose therapeutic massage for everyday tension, stiffness, or time to slow down. Butsaba asks about your preferences and relevant concerns before beginning, then checks in during the session. You can request lighter pressure, a different focus, or a pause at any time.",
         "People often book therapeutic massage for everyday tension and stiffness, or simply for time to slow down. Before I begin, I ask about your preferences and anything I should know about your health. During the session you can ask for lighter pressure, a different focus, or a pause at any time."),
        ("If you have an ongoing health condition or a new symptom, tell Butsaba during intake so she can work within massage scope and suggest medical guidance when appropriate.",
         "If you have an ongoing health condition or a new symptom, tell me at the start. I will keep the work within the scope of massage, and I will tell you if I think you should check with a doctor first."),
        ("\"answer\":\"Start by telling Butsaba what you would like from the session. She will discuss the areas of focus, pace, and pressure with you before the work begins.\"",
         "\"answer\":\"Start by telling me what you would like from the session. Before I begin, I talk through the areas of focus, pace, and pressure with you.\""),
        ("\"answer\":\"People respond differently to massage. Butsaba will ask how the session feels and adjust the approach; no particular result or timeline is promised.\"",
         "\"answer\":\"People respond differently to massage, so I can’t promise a particular result or timeline. I will ask how the session feels and adjust as I go.\""),
        ("\"answer\":\"There is no fixed schedule for everyone. You can discuss your goals with Butsaba and decide whether another session makes sense for you.\"",
         "\"answer\":\"There is no schedule that suits everyone. You decide whether another session makes sense, and I am glad to talk through your goals with you first.\""),
        ("\"answer\":\"Yes. You can book time for relaxation even if you do not have a particular area of concern. Tell Butsaba what kind of session would feel useful.\"",
         "\"answer\":\"Yes. You don’t need a particular area of concern to book. Tell me what kind of session would feel good, and I will plan around that.\""),
        ("\"answer\":\"If headaches are part of your reason for visiting, mention them during intake. Butsaba can discuss massage for neck and shoulder tension within her scope. Seek medical advice for new, severe, or concerning headaches.\"",
         "\"answer\":\"If headaches are part of why you are coming in, mention them at the start. I can work on neck and shoulder tension within the scope of massage. For new, severe, or worrying headaches, please see a doctor first.\""),
        ("Butsaba begins with a brief intake to understand where you're holding tension, what's been bothering you, and what you're hoping to feel differently after the session.",
         "I begin with a short conversation about where you’re holding tension, what’s been bothering you, and how you would like to feel when the session ends."),
        ("Butsaba tailors the approach to your goals for the session — whether that's full-body relaxation, focused work on a specific area, or a combination of both.",
         "From there, I plan the session around your goals: full-body relaxation, focused work on one area, or some of each."),
        ("Butsaba works through the body with intentional technique — addressing both surface tension and deeper patterns of tightness, adjusting pressure and pace as the session progresses.",
         "I work from surface tension toward deeper tightness, adjusting pressure and pace as the session goes on."),
        ("Butsaba closes the session by asking how the work felt and discussing any questions about future sessions.",
         "At the end, I ask how the work felt and answer any questions about future sessions."),
    ],
    # ---------------------------------------------------------------- Deep Tissue
    1091: BANNER + [
        ("Deep tissue massage uses focused, deliberate pressure for areas of persistent muscle tightness. Butsaba begins by asking about your goals and pressure preferences, then checks in and adjusts the work to your comfort. Deep pressure is optional, and you can ask for a lighter approach at any time.",
         "Deep tissue massage uses slow, deliberate pressure on muscles that stay tight. I begin by asking about your goals and how much pressure you like, then check in as I work. Deep pressure is never required: you can ask me to lighten up at any time, and I will."),
        ("Clients may choose deep tissue massage when an area feels persistently tight after work, exercise, or daily routines. Butsaba focuses on the areas you identify and adapts pressure to your feedback.",
         "People often choose deep tissue massage when an area stays tight after work, exercise, or daily routines. I focus on the areas you point out and adjust pressure as you tell me how it feels."),
        ("Butsaba begins with a brief intake to understand your areas of concern, pressure preferences, and any relevant history — so the session starts with purpose, not guesswork.",
         "I begin by asking where you want me to focus, how much pressure you like, and anything about your health or past injuries I should know."),
        ("Based on your intake, Butsaba determines which areas need the most attention and selects the appropriate pressure and technique combination for your session.",
         "From your answers, I decide where to spend the most time and which techniques and pressure to use, and I tell you the plan before I start."),
        ("Butsaba uses focused strokes and sustained pressure where you have asked for attention, adjusting the technique as you give feedback.",
         "I use focused strokes and sustained pressure where you asked for attention, and I change the technique as you give feedback."),
        ("After the session, Butsaba can discuss how the work felt and any questions you have about future sessions.",
         "Afterward, I ask how the work felt and answer any questions about future sessions."),
    ],
    # ---------------------------------------------------------------- Thai
    1160: BANNER + [
        ("Thai massage combines rhythmic compression, gentle rocking, and assisted stretching in a session that brings movement and massage together. Butsaba adapts the pace and range of each movement to your comfort, creating time to slow down while giving stiff areas thoughtful attention.",
         "Thai massage brings movement and massage together through rhythmic compression, gentle rocking, and assisted stretching. It is my specialty. I set the pace and range of each movement by what is comfortable for you, so you have time to slow down while stiff areas get careful attention."),
        ("As the session progresses, Butsaba may apply steady pressure and guide comfortable stretches to help you notice where your body feels restricted. You can tell her what feels good, ask for a lighter touch, or skip a movement at any point. The goal is a supported experience that helps you feel more at ease in your body.",
         "As the session goes on, I apply steady pressure and guide you through stretches that can help you notice where your body feels restricted. Tell me what feels good, ask for a lighter touch, or skip a movement at any point. I want you to feel supported the whole way through."),
        ("Thai massage is often chosen by people who enjoy hands-on bodywork but also want gentle movement. Every session is tailored to the person on the table, so the work can be restful, more active, or a blend of both.",
         "People who enjoy hands-on bodywork but also want gentle movement often choose Thai massage. You stay fully clothed, so wear something loose and comfortable. I shape each session around the person in front of me, so the work can be restful, more active, or a blend of both."),
        ("Clients often choose Thai massage when long hours at a desk, travel, exercise, or everyday routines leave them feeling stiff and ready to move more freely.",
         "People often come to Thai massage when long hours at a desk, travel, exercise, or everyday routines leave them stiff and wanting to move more freely."),
        ("Butsaba begins with your goals and comfort level. If your shoulders, hips, or back feel tight, she can give those areas more attention while still keeping the session balanced. Stretches are guided rather than forced, and you can ask for any movement to be changed or left out.",
         "I begin with your goals and comfort level. If your shoulders, hips, or back feel tight, I can spend more time there and still give the rest of your body its share. I guide stretches and never force them, and you can ask me to change or leave out any movement."),
        ("Whether you are trying Thai massage for the first time or returning to a familiar practice, tell Butsaba about any health concerns or movement limits before the session. Together you can decide what feels suitable that day.",
         "Whether this is your first Thai massage or you have had many, tell me about any health concerns or movement limits before the session begins. Then you and I can decide what feels right that day."),
        ("Butsaba starts with a brief conversation about how you are feeling, which areas feel stiff, and how much movement and pressure you would enjoy.",
         "I start with a short conversation about how you are feeling, which areas feel stiff, and how much movement and pressure you would enjoy."),
        ("Together you choose a comfortable pace. Butsaba adapts the mix of rhythmic pressure, gentle rocking, and assisted stretches to your goals for the session.",
         "You choose a comfortable pace, and I adjust the mix of rhythmic pressure, gentle rocking, and assisted stretches to your goals for the session."),
        ("Butsaba guides each movement and checks in as needed.",
         "I guide each movement and check in as I go."),
        ("Before you leave, Butsaba can suggest simple ways to keep the sense of ease from your session, based on how your body responded.",
         "Before you leave, I can suggest a few simple ways to hold on to that sense of ease, based on how your body responded."),
    ],
    # ---------------------------------------------------------------- Work With Me
    1165: [
        ("Butsaba brings traditional Thai bodywork, therapeutic and relaxation massage, and a listening ear to each session. She is interested in joining a Portland-area practice that values thoughtful, individual care.",
         "I offer traditional Thai bodywork, therapeutic massage, and relaxation massage, and I start every session by listening. I would like to join a Portland-area practice that gives each client individual attention."),
        ("A career shaped by people and practice.", "From export sales to massage."),
        ("Before massage, Butsaba worked in export sales and customer service for Thai manufacturers. That experience informs the way she listens, communicates clearly, and follows through with clients.",
         "Before massage, I worked in export sales and customer service for Thai manufacturers. That work taught me to listen carefully, explain things plainly, and follow through on what I promise. I bring the same habits to my clients."),
        ("Since 2020, she has worked in a Thai bodywork studio, a busy Chicago massage setting, and a practice of her own.",
         "Since 2020, I have worked in a Thai bodywork salon, in a busy Chicago massage practice, and in a practice of my own."),
        (", her clients included hotel guests and business travelers. When the time together may be a single visit, the opening conversation matters.",
         ", my clients have included hotel guests and business travelers, people I might see only once. When a session may be the only one, the first conversation matters."),
        (">Her approach<", ">My approach<"),
        ("Butsaba begins with what brought someone in, then lets their comfort and feedback guide the work.",
         "I begin with what brought someone in, then let their comfort and feedback guide the work."),
        ("Range and care for your team.", "What I bring to a team."),
        ("Sessions respond to each client’s goals and comfort.",
         "I shape each session around the client’s goals and comfort."),
        ("Butsaba founded <a href=\"/\">Thai Solitude</a> and handles consultations, scheduling, client communication, and day-to-day operations. She brings that service experience into a shared practice.",
         "I founded <a href=\"/\">Thai Solitude</a> and run it myself: consultations, scheduling, client communication, and day-to-day operations. I would bring that experience to a shared practice."),
        ("If Butsaba’s approach sounds like a fit for your team, she would welcome a conversation about your practice and the care you offer.",
         "If my approach sounds like a fit for your team, I would welcome a conversation about your practice and the care you offer."),
        ("Butsaba will talk through what you want and adapt the work to your comfort.",
         "I will talk through what you want and adapt the work to your comfort."),
    ],
    # ---------------------------------------------------------------- Contact
    301: BANNER,
}

# Work With Me has two identical "Contact Butsaba" buttons; replaced with an exact count.
MULTI = {
    1165: [(">Contact Butsaba<", ">Contact Me<", 2)],
}

# Shared site chrome stored in options.
OPTIONS = {
    # astra-settings[footer-copyright-editor]: approved first-person variant of the scope line
    # (Decisions Register, "Voice interaction").
    "astra-settings.footer-copyright-editor": [
        ("Massage is not a substitute for medical care. Butsaba does not diagnose or treat medical conditions.",
         "Massage is not a substitute for medical care. I do not diagnose or treat medical conditions."),
    ],
    # widget_block[7]: the "Coming Soon at Thai Solitude" call to action above the footer.
    "widget_block.7.content": [
        ("Massage sessions are planned to begin in November. Have a question or want to ask about availability? Get in touch with Butsaba.",
         "I plan to begin massage sessions in November. Have a question, or want to ask about availability? Get in touch with me."),
    ],
}
