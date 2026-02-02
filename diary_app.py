from datetime import datetime

def create_entry():
    """Allow user to write a diary or dream entry"""
    print("\n--- Create Dream / Diary Entry ---")
    entry_text = input("Write your dream or diary entry:\n")

    timestamp = datetime.now().strftime("%Y-%m-%d %H:%M:%S")

    with open("entries.txt", "a") as file:
        file.write(f"[{timestamp}]\n")
        file.write(entry_text + "\n")
        file.write("-" * 40 + "\n")

    print("\nEntry saved successfully!\n")


def view_entries():
    """Display saved diary entries"""
    print("\n--- View Saved Entries ---")
    try:
        with open("entries.txt", "r") as file:
            print(file.read())
    except FileNotFoundError:
        print("No entries found yet.")


def main_menu():
    """Main menu for the diary application"""
    while True:
        print("\n=== Dream Notebook ===")
        print("1. Create Dream/Diary Entry")
        print("2. View Entries")
        print("3. Exit")

        choice = input("Choose an option: ")

        if choice == "1":
            create_entry()
        elif choice == "2":
            view_entries()
        elif choice == "3":
            print("Goodbye!")
            break
        else:
            print("Invalid choice. Please try again.")


if __name__ == "__main__":
    main_menu()
