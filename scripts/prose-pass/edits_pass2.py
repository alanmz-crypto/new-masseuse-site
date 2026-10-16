"""Second, light pass (Mode B, 2026-10-13). Each edit names the defect it fixes.
About's first-person value statements are deliberately untouched.
"""
from edits import BANNER

PAGES = {
    # Pass 1 never applied the banner to Work With Me; it still read "get in touch with Butsaba".
    1165: list(BANNER),
    # Home: the About card repeated the listening promise already made by the hero line
    # and the "Clear Communication" card on the same screen.
    296: [
        (" Before I begin, I ask what you want from the session, and I keep adjusting as you tell me how it feels.", ""),
    ],
    # Services: "session" three times in one sentence.
    299: [
        ("and I will adjust as the session goes on.", "and I will adjust as I go."),
    ],
    # "Treatment Plan" contradicts the footer ("I do not diagnose or treat medical
    # conditions"); Services already says "Planning the Session".
    1107: [("Personalized Treatment Plan", "Planning the Session")],
    1091: [("Targeted Treatment Plan", "Planning the Session")],
    # Thai: "People ... often choose" opened this paragraph and "People often come" opens
    # the very next section.
    1160: [
        ("People who enjoy hands-on bodywork but also want gentle movement often choose Thai massage.",
         "Thai massage suits people who enjoy hands-on bodywork but also want gentle movement."),
    ],
}
