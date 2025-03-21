import sqlite3
import asyncio
from aiogram import Bot, Dispatcher, types
from aiogram.types import Message, InlineKeyboardMarkup, InlineKeyboardButton, ReactionTypeEmoji
from aiogram.filters import Command
from aiogram.enums import ParseMode
from aiogram.client.default import DefaultBotProperties  

# ✅ TOKEN va ADMIN ID
TOKEN = "7928900640:AAE1YKQUQiiTTfFen1pAGyiu6Z48aA6gCSY"
ADMIN_ID = 1276742

# Bot va Dispatcher
bot = Bot(token=TOKEN, default=DefaultBotProperties(parse_mode=ParseMode.HTML))
dp = Dispatcher()

# ✅ SQLite BAZA YARATISH
conn = sqlite3.connect("bot_data.db")
cursor = conn.cursor()

cursor.execute("""CREATE TABLE IF NOT EXISTS channels (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT UNIQUE
)""")

cursor.execute("""CREATE TABLE IF NOT EXISTS reactions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    emoji TEXT
)""")
conn.commit()

# ✅ FUNKSIYA: Kanallarni olish
def get_channels():
    cursor.execute("SELECT username FROM channels")
    return [row[0] for row in cursor.fetchall()]

# ✅ FUNKSIYA: Reaksiyalarni olish
def get_reactions():
    cursor.execute("SELECT emoji FROM reactions")
    return [row[0] for row in cursor.fetchall()]

# ✅ ADMIN PANEL: Boshlash
@dp.message(Command("admin"))
async def admin_panel(message: Message):
    if message.from_user.id != ADMIN_ID:
        return
    keyboard = InlineKeyboardMarkup(inline_keyboard=[
        [InlineKeyboardButton(text="➕ Kanal qo‘shish", callback_data="add_channel")],
        [InlineKeyboardButton(text="❌ Kanalni o‘chirish", callback_data="del_channel")],
        [InlineKeyboardButton(text="📃 Qo‘shilgan kanallar", callback_data="list_channels")]
    ])
    await message.reply("🔧 Admin panel", reply_markup=keyboard)

# ✅ KANAL QO‘SHISH
@dp.message(lambda message: message.text.startswith("@") and message.from_user.id == ADMIN_ID)
async def add_channel(message: Message):
    channel = message.text.strip()

    try:
        chat = await bot.get_chat(channel)
        chat_id = chat.id

        cursor.execute("INSERT INTO channels (username) VALUES (?)", (channel,))
        conn.commit()
        await message.reply(f"✅ `{channel}` kanali qo‘shildi!\n📡 Kanal ID: `{chat_id}`", parse_mode="Markdown")

    except sqlite3.IntegrityError:
        await message.reply("⚠️ Bu kanal oldin qo‘shilgan!")

    except Exception as e:
        await message.reply(f"❌ Xatolik: `{e}`\n\n1️⃣ Bot kanalda admin ekanligini tekshiring!\n2️⃣ Kanal to‘g‘ri kiritilganmi, tekshiring.", parse_mode="Markdown")

# ✅ KANALNI O‘CHIRISH
@dp.message(lambda message: message.text.startswith("-") and message.from_user.id == ADMIN_ID)
async def delete_channel(message: Message):
    channel = message.text.strip()
    
    cursor.execute("DELETE FROM channels WHERE username=?", (channel,))
    conn.commit()
    await message.reply(f"❌ `{channel}` kanali o‘chirildi!")

# ✅ QO‘SHILGAN KANALLARNI KO‘RISH
@dp.callback_query(lambda call: call.data == "list_channels")
async def list_channels(call: types.CallbackQuery):
    channels = get_channels()
    if not channels:
        await call.message.answer("📭 Hech qanday kanal qo‘shilmagan!")
    else:
        await call.message.answer("\n".join([f"📌 `{ch}`" for ch in channels]), parse_mode="Markdown")

# ✅ POSTLARGA REAKSIYA BOSISH
async def react_to_posts():
    while True:
        channels = get_channels()
        reactions = get_reactions()
        if not reactions:
            reactions = ["👍", "❤️", "🔥", "😂", "😃", "💯", "👏"]  # Default emojis

        for channel in channels:
            try:
                chat = await bot.get_chat(channel)
                messages = await bot.get_chat_history(chat.id, limit=1)
                
                if messages and messages[0].message_id:
                    msg_id = messages[0].message_id
                    
                    for i in range(30):  # 30 ta reaksiya bosish
                        emoji = reactions[i % len(reactions)]
                        await bot.set_message_reaction(
                            chat_id=chat.id,
                            message_id=msg_id,
                            reaction=[ReactionTypeEmoji(emoji=emoji)]
                        )
                        await asyncio.sleep(1)
            except Exception as e:
                print(f"⚠️ Xatolik: {e}")

        await asyncio.sleep(300)

# ✅ BOTNI ISHGA TUSHIRISH
async def main():
    asyncio.create_task(react_to_posts())
    await dp.start_polling(bot)

if __name__ == "__main__":
    asyncio.run(main())
