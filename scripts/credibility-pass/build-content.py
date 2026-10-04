#!/usr/bin/env python3
"""Tighten copy in exported staging12 WordPress blocks without changing layout."""

import html
import json
import re
from html.parser import HTMLParser
from pathlib import Path

OUTPUT = Path(__file__).resolve().parent
SOURCE = OUTPUT / 'source'


class TextOnlyParser(HTMLParser):
    def __init__(self):
        super().__init__(convert_charrefs=True)
        self.parts = []

    def handle_data(self, data):
        if data.strip():
            self.parts.append(data.strip())


def text_content(fragment):
    parser = TextOnlyParser()
    parser.feed(fragment)
    return ' '.join(parser.parts)


def replace_once(source, old, new):
    count = source.count(old)
    if count != 1:
        raise ValueError(f'Expected one exact image attribute {old!r}, found {count}')
    return source.replace(old, new, 1)


def replace_text_element(source, tag, starts, replacement):
    pattern = re.compile(rf'(<{tag}\b[^>]*>)(.*?)(</{tag}>)', re.S)
    count = 0

    def edit(match):
        nonlocal count
        text = text_content(match.group(2))
        if text.startswith(starts):
            count += 1
            return match.group(1) + html.escape(replacement, quote=False) + match.group(3)
        return match.group(0)

    result = pattern.sub(edit, source)
    if count != 1:
        raise ValueError(f'Expected one {tag} beginning {starts!r}, found {count}')
    return result


def replace_faq(source, question, *, new_question=None, answer=None):
    pattern = re.compile(r'<!-- wp:uagb/faq-child (\{.*?\}) /-->')
    count = 0

    def edit(match):
        nonlocal count
        data = json.loads(match.group(1))
        if data.get('question') != question:
            return match.group(0)
        count += 1
        if new_question:
            data['question'] = new_question
        if answer:
            data['answer'] = answer
        return '<!-- wp:uagb/faq-child ' + json.dumps(data, ensure_ascii=False, separators=(',', ':')) + ' /-->'

    result = pattern.sub(edit, source)
    if count != 1:
        raise ValueError(f'Expected one FAQ {question!r}, found {count}')
    return result


def edit_page(page_id, edits, faqs=()):
    path = SOURCE / f'page-{page_id}.html'
    source = path.read_text()
    for tag, starts, replacement in edits:
        source = replace_text_element(source, tag, starts, replacement)
    for question, new_question, answer in faqs:
        source = replace_faq(source, question, new_question=new_question, answer=answer)
    if page_id == 298:
        for attribute in ('alt', 'title'):
            source = replace_once(source, f'"{attribute}":"butsaba avatar"',
                                  f'"{attribute}":"Portrait of Butsaba Dusrai"')
            source = replace_once(source, f'{attribute}="butsaba avatar"',
                                  f'{attribute}="Portrait of Butsaba Dusrai"')
    (OUTPUT / f'page-{page_id}.html').write_text(source)


edit_page(298, [
    ('h1', 'Experience Deep Relaxation', 'Meet Butsaba Dusrai'),
    ('h3', 'Butsaba Dusrai is a licensed massage therapist', 'Butsaba Dusrai is an Oregon Licensed Massage Therapist with hands-on experience since 2020.'),
    ('p', 'At the center of every session', 'I trained in massage therapy at Estelle Medical Academy and in traditional Thai bodywork at Still Light Center. My work includes Thai, therapeutic, and relaxation massage. I begin by listening to your goals, then adapt the pressure, pace, and focus to your comfort.'),
    ('p', 'Clients regularly experience', 'Thai Solitude is my independent practice, shaped by traditional Thai bodywork and a personal approach to therapeutic massage. Each session starts with a conversation and stays responsive to how you feel.'),
    ('p', 'What began as small-scale', 'I founded Thai Solitude in 2024 after beginning my massage work in 2020. Alongside running the practice, I have worked in a Thai bodywork studio and a busy massage setting. I look forward to welcoming Portland clients when sessions begin in November.'),
], [
    ('My Commitment to Your Wellness', None, 'My aim is to create a calm, professional space where you can share your goals and feel comfortable speaking up. I tailor the pace, pressure, and focus of each session to you.'),
    ('Empowering Wellness Through Expert Massage Therapy', 'Massage as Part of Everyday Care', 'I want massage to feel approachable, whether you come in with a specific area of tension or simply need time to slow down.'),
    ('Values That Guide Every Session', None, 'Trust, professionalism, and empathy shape my work. I listen to your goals, check in about comfort, and make room for your feedback throughout the session.'),
])

edit_page(296, [
    ('p', 'Experience personalized massage therapy designed', 'Personalized Thai and therapeutic massage, shaped around your goals and comfort in Portland’s Mt. Tabor area.'),
    ('p', 'Discover the healing power', 'Discover a thoughtful approach to massage therapy.'),
    ('p', 'Founded by licensed therapist', 'Founded by Butsaba Dusrai, an Oregon Licensed Massage Therapist, Thai Solitude brings Thai bodywork and therapeutic massage together. Butsaba listens to your goals and adapts each session to your comfort.'),
    ('p', 'Gentle to moderate pressure designed', 'Gentle to moderate pressure for clients seeking a quieter session and time to relax.'),
    ('p', 'Intensive techniques targeting', 'Focused pressure for areas of persistent tightness, adapted to your comfort and feedback.'),
    ('p', 'Benefit from licensed massage therapy', 'Butsaba brings massage experience since 2020 and tailors each session to your goals and preferences.'),
    ('p', 'Skip the commute.', 'Tell Butsaba what brings you in. She will listen, explain the approach, and welcome feedback throughout the session.'),
    ('p', 'Experience a holistic approach', 'Thai bodywork and therapeutic techniques offer different ways to address your goals, from guided movement to a calmer massage session.'),
    ('p', 'Mobile Service', 'Clear Communication'),
])

edit_page(299, [
    ('p', 'Butsaba’s signature approach', 'Therapeutic massage uses focused pressure and a pace adapted to your comfort. Tell Butsaba where you feel tension and what you would like from the session; she can adjust the work as you go.'),
    ('p', 'Designed for persistent knots', 'Deep tissue massage uses focused, deliberate pressure for areas of persistent tightness. Butsaba checks in about pressure and adapts the work to your comfort.'),
    ('p', 'A personalized treatment plan is crafted', 'Butsaba uses your goals and preferences to choose a comfortable pace, pressure, and focus for the session.'),
    ('p', 'After your session, Butsaba provides guidance', 'After your session, Butsaba can discuss how the work felt and whether a follow-up session would be useful.'),
])

edit_page(1091, [
    ('p', 'Deep tissue massage is a therapeutic technique', 'Deep tissue massage uses focused, deliberate pressure for areas of persistent muscle tightness. Butsaba begins by asking about your goals and pressure preferences, then checks in and adjusts the work to your comfort. Deep pressure is optional, and you can ask for a lighter approach at any time.'),
    ('p', 'Many clients schedule an appointment', 'Clients may choose deep tissue massage when an area feels persistently tight after work, exercise, or daily routines. Butsaba focuses on the areas you identify and adapts pressure to your feedback. If you have new numbness, muscle weakness, unexplained swelling, severe pain, or pain that radiates down an arm or leg, seek medical evaluation before booking massage.'),
    ('p', 'Butsaba applies focused strokes', 'Butsaba uses focused strokes and sustained pressure where you have asked for attention, adjusting the technique as you give feedback.'),
    ('p', 'After your session, Butsaba shares care recommendations', 'After the session, Butsaba can discuss how the work felt and any questions you have about future sessions.'),
])

edit_page(1107, [
    ('p', 'Therapeutic massage is an approach', 'Therapeutic massage combines focused work with time to relax. Butsaba listens to what brings you in, then adapts pressure, pace, and areas of focus to your comfort. You can ask for attention to a particular area or choose a broader session; the plan can change as you give feedback.'),
    ('p', 'Clients choose therapeutic massage for both', 'Clients may choose therapeutic massage for everyday tension, stiffness, or time to slow down. Butsaba asks about your preferences and relevant concerns before beginning, then checks in during the session. You can request lighter pressure, a different focus, or a pause at any time.'),
    ('p', 'Therapeutic massage can benefit a wide range', 'Therapeutic massage can be adapted to many goals, from a calm session to focused work on areas of tension. If you have an ongoing health condition or a new symptom, tell Butsaba during intake so she can work within massage scope and suggest medical guidance when appropriate.'),
    ('p', 'Butsaba closes the session', 'Butsaba closes the session by asking how the work felt and discussing any questions about future sessions.'),
], [
    ('What Is the Difference Between Therapeutic and Relaxation Massage?', None, 'Relaxation massage prioritizes a calm experience. Therapeutic massage can give more attention to a particular area of tension while still adapting pressure and pace to your comfort.'),
    ('How Long Does a Therapeutic Massage Session Last?', 'How Is a Therapeutic Session Planned?', 'Start by telling Butsaba what you would like from the session. She will discuss the areas of focus, pace, and pressure with you before the work begins.'),
    ('Will I Feel the Results Right Away?', None, 'People respond differently to massage. Butsaba will ask how the session feels and adjust the approach; no particular result or timeline is promised.'),
    ('How Often Should I Come In for Therapeutic Massage?', None, 'There is no fixed schedule for everyone. You can discuss your goals with Butsaba and decide whether another session makes sense for you.'),
    ('Is Therapeutic Massage Appropriate If I Have No Specific Complaint?', None, 'Yes. You can book time for relaxation even if you do not have a particular area of concern. Tell Butsaba what kind of session would feel useful.'),
    ('Can Therapeutic Massage Help With Tension Headaches?', None, 'If headaches are part of your reason for visiting, mention them during intake. Butsaba can discuss massage for neck and shoulder tension within her scope. Seek medical advice for new, severe, or concerning headaches.'),
])

contact_source = (SOURCE / 'page-301.html').read_text()
old_sms = 'sms:+13125394328&amp;body=Hi%20Butsaba!%20I%20have%20a%20question%20about%20November%20availability.'
if contact_source.count(old_sms) != 1:
    raise ValueError('Expected the existing Contact phone link once')
contact_source = contact_source.replace(old_sms, 'tel:+13125394328')
old_email = '<p class="uagb-heading-text">butsabad@gmail.com</p>'
if contact_source.count(old_email) != 1:
    raise ValueError('Expected the existing Contact email once')
contact_source = contact_source.replace(old_email, '<p class="uagb-heading-text"><a href="mailto:butsabad@gmail.com" style="font-size:clamp(16px,4vw,22px);text-transform:none;">butsabad@gmail.com</a></p>')
if contact_source.count('"blockVisibility":false') != 1:
    raise ValueError('Expected one hidden email card')
contact_source = contact_source.replace('"blockVisibility":false', '"blockVisibility":true')
contact_source, removed_social = re.subn(
    r'<!-- wp:uagb/container \{"block_id":"7c79bd17".*?<!-- /wp:uagb/container -->',
    '', contact_source, count=1, flags=re.S,
)
if removed_social != 1:
    raise ValueError('Expected the generic Instagram card once')
(OUTPUT / 'page-301.html').write_text(contact_source)

print('Built local page content for About, Home, Services, two service details, and Contact.')
